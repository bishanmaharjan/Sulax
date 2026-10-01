<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$product_id = (int)($_POST["product_id"] ?? 0);
$rating = (int)($_POST["rating"] ?? 0);
$comment = trim($_POST["comment"] ?? "");

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    header("Location: product-details.php?id=" . $product_id . "&review_error=1");
    exit();
}

if (mb_strlen($comment) > 1000) {
    $comment = mb_substr($comment, 0, 1000);
}

// A customer can review a product only after a delivered order containing it.
$stmt = $conn->prepare(
    "SELECT oi.order_id
     FROM order_items oi
     INNER JOIN orders o ON o.id = oi.order_id
     WHERE oi.product_id = ?
       AND o.user_id = ?
       AND o.order_status = 'Delivered'
     ORDER BY o.id DESC
     LIMIT 1"
);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$purchase = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$purchase) {
    header("Location: product-details.php?id=" . $product_id . "&review_error=purchase");
    exit();
}

$order_id = (int)$purchase["order_id"];

$check = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

if ($existing) {
    $stmt = $conn->prepare(
        "UPDATE reviews SET rating = ?, comment = ?, order_id = ? WHERE id = ? AND user_id = ?"
    );
    $review_id = (int)$existing["id"];
    $stmt->bind_param("isiii", $rating, $comment, $order_id, $review_id, $user_id);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO reviews (user_id, product_id, order_id, rating, comment) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("iiiis", $user_id, $product_id, $order_id, $rating, $comment);
}

if (!$stmt->execute()) {
    $stmt->close();
    header("Location: product-details.php?id=" . $product_id . "&review_error=save");
    exit();
}
$stmt->close();

// Keep the product's stored rating synchronized with customer reviews.
$avg = $conn->prepare("SELECT AVG(rating) AS avg_rating FROM reviews WHERE product_id = ?");
$avg->bind_param("i", $product_id);
$avg->execute();
$average = (float)($avg->get_result()->fetch_assoc()["avg_rating"] ?? 0);
$avg->close();

$update = $conn->prepare("UPDATE products SET rating = ? WHERE id = ?");
$update->bind_param("di", $average, $product_id);
$update->execute();
$update->close();

header("Location: product-details.php?id=" . $product_id . "&review=success#reviews");
exit();
?>
