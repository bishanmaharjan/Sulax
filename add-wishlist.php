<?php

session_start();

require_once "config/database.php";


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


$user_id =
    $_SESSION["user_id"];


$product_id =
    intval($_GET["id"] ?? 0);


if ($product_id <= 0) {

    header("Location: index.php");

    exit();

}


// ==========================================
// CHECK PRODUCT
// ==========================================

$stmt = $conn->prepare(
    "SELECT id
     FROM products
     WHERE id = ?"
);

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$stmt->close();


if ($result->num_rows === 0) {

    die("Product not found.");

}


// ==========================================
// ADD TO WISHLIST
// ==========================================

$stmt = $conn->prepare(

    "INSERT IGNORE INTO wishlist
     (user_id, product_id)
     VALUES (?, ?)"

);


$stmt->bind_param(
    "ii",
    $user_id,
    $product_id
);


$stmt->execute();

$stmt->close();


// ==========================================
// RETURN
// ==========================================

$redirect =
    $_GET["redirect"] ?? "index.php";


if ($redirect === "product") {

    header(
        "Location: product-details.php?id=" .
        $product_id
    );

} else {

    header(
        "Location: index.php"
    );

}

exit();

?>