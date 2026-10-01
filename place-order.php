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

$user_id = $_SESSION["user_id"];


// ==========================================
// CHECK REQUEST
// ==========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: checkout.php");
    exit();

}


// ==========================================
// GET CUSTOMER INFORMATION
// ==========================================

$name = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$payment = trim($_POST["payment"] ?? "");


// ==========================================
// VALIDATE CUSTOMER INFORMATION
// ==========================================

$cleanPhone = preg_replace('/[\s-]/', '', $phone);
$allowedPayments = ["Cash on Delivery", "Online Payment"];

if (
    $name === "" || mb_strlen($name) < 2 || mb_strlen($name) > 100 ||
    $phone === "" || !preg_match('/^\+?[0-9]{7,15}$/', $cleanPhone) ||
    $address === "" || mb_strlen($address) > 1000 ||
    !in_array($payment, $allowedPayments, true)
) {
    die("Please enter valid customer and payment information.");
}


// ==========================================
// GET CART FROM CHECKOUT
// ==========================================

$cart_json = $_POST["cart"] ?? "";

$cart = json_decode($cart_json, true);

if (!is_array($cart) || count($cart) === 0 || count($cart) > 50) {

    die("Cart is empty.");

}


// ==========================================
// START DATABASE TRANSACTION
// ==========================================

$conn->begin_transaction();


try {


    // ==========================================
    // DELIVERY CHARGE
    // ==========================================

    $delivery_charge = 100;

    $items_total = 0;

    $order_items = [];


    // ==========================================
    // CHECK EACH CART PRODUCT
    // ==========================================

    foreach ($cart as $item) {


        // Product ID
        $product_id = intval($item["id"] ?? 0);


        // Quantity
        $quantity = intval($item["quantity"] ?? 0);


        // Size
        $size = trim($item["size"] ?? "");


        // Validate product ID
        if ($product_id <= 0) {

            throw new Exception(
                "Invalid product selected."
            );

        }


        // Validate quantity
        if ($quantity <= 0) {

            throw new Exception(
                "Invalid product quantity."
            );

        }


        // Validate size (the store currently offers EU/UK-style numeric sizes 6-11)
        if (!in_array($size, ["6", "7", "8", "9", "10", "11"], true)) {

            throw new Exception(
                "Please select a valid shoe size (6-11)."
            );

        }


        // ==========================================
        // GET REAL PRODUCT DATA FROM DATABASE
        // ==========================================

        $product_stmt = $conn->prepare(
            "SELECT id, name, price, stock
             FROM products
             WHERE id = ?
             FOR UPDATE"
        );


        $product_stmt->bind_param(
            "i",
            $product_id
        );


        $product_stmt->execute();


        $result = $product_stmt->get_result();


        if ($result->num_rows === 0) {

            throw new Exception(
                "Product not found."
            );

        }


        $product = $result->fetch_assoc();


        $product_name = $product["name"];

        $price = floatval($product["price"]);

        $stock = intval($product["stock"]);


        // ==========================================
        // CHECK STOCK
        // ==========================================

        if ($stock < $quantity) {

            throw new Exception(
                "Sorry! " .
                htmlspecialchars($product_name) .
                " has only " .
                $stock .
                " item(s) available."
            );

        }


        // ==========================================
        // CALCULATE ITEM TOTAL
        // ==========================================

        $item_total =
            $price * $quantity;


        $items_total += $item_total;


        // ==========================================
        // STORE ITEM INFORMATION
        // ==========================================

        $order_items[] = [

            "product_id" => $product_id,

            "product_name" => $product_name,

            "price" => $price,

            "size" => $size,

            "quantity" => $quantity

        ];


        $product_stmt->close();

    }


    // ==========================================
    // FINAL ORDER TOTAL
    // ==========================================

    $total_amount =
        $items_total + $delivery_charge;


    // ==========================================
    // INSERT ORDER
    // ==========================================

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
        (
            user_id,
            customer_name,
            phone,
            address,
            payment_method,
            total_amount,
            order_status
        )
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
    );


    $order_stmt->bind_param(
        "issssd",
        $user_id,
        $name,
        $phone,
        $address,
        $payment,
        $total_amount
    );


    if (!$order_stmt->execute()) {

        throw new Exception(
            "Failed to create order."
        );

    }


    // Get Order ID
    $order_id = $conn->insert_id;


    $order_stmt->close();


    // ==========================================
    // INSERT ORDER ITEMS
    // ==========================================

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
        (
            order_id,
            product_id,
            product_name,
            price,
            size,
            quantity
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );


    // ==========================================
    // REDUCE PRODUCT STOCK
    // ==========================================

    $stock_stmt = $conn->prepare(
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ?
         AND stock >= ?"
    );


    foreach ($order_items as $item) {


        // Values
        $product_id =
            $item["product_id"];

        $product_name =
            $item["product_name"];

        $price =
            $item["price"];

        $size =
            $item["size"];

        $quantity =
            $item["quantity"];


        // ------------------------------------------
        // INSERT ORDER ITEM
        // ------------------------------------------

        $item_stmt->bind_param(
            "iisdsi",
            $order_id,
            $product_id,
            $product_name,
            $price,
            $size,
            $quantity
        );


        if (!$item_stmt->execute()) {

            throw new Exception(
                "Failed to save order item."
            );

        }


        // ------------------------------------------
        // REDUCE STOCK
        // ------------------------------------------

        $stock_stmt->bind_param(
            "iii",
            $quantity,
            $product_id,
            $quantity
        );


        if (!$stock_stmt->execute()) {

            throw new Exception(
                "Failed to update product stock."
            );

        }


        if ($stock_stmt->affected_rows === 0) {

            throw new Exception(
                "Product stock is no longer available."
            );

        }

    }


    $item_stmt->close();

    $stock_stmt->close();


    // ==========================================
    // COMMIT TRANSACTION
    // ==========================================

    $conn->commit();


}
catch (Exception $e) {


    // ==========================================
    // ROLLBACK IF ERROR
    // ==========================================

    $conn->rollback();


    die(
        "Order could not be placed: " .
        htmlspecialchars($e->getMessage())
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Successful - Sulax Shoes Collection</title>

    <link rel="stylesheet"
          href="style.css">

    <style>

        .order-success {

            max-width: 600px;

            margin: 80px auto;

            padding: 40px;

            text-align: center;

            background: white;

            border-radius: 10px;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, 0.10);

        }


        .success-icon {

            width: 80px;

            height: 80px;

            margin: 0 auto 20px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background: #28a745;

            color: white;

            font-size: 50px;

            font-weight: bold;

        }


        .order-success h1 {

            margin-bottom: 15px;

            color: #222;

        }


        .order-success p {

            color: #555;

            margin: 10px 0;

        }


        .order-id {

            font-size: 20px;

            margin: 20px 0;

        }


        .order-success a {

            display: inline-block;

            margin-top: 20px;

            padding: 12px 25px;

            background: #f85606;

            color: white;

            text-decoration: none;

            border-radius: 5px;

        }


        .order-success a:hover {

            background: #d94700;

        }

    </style>

</head>


<body>


<div class="order-success">


    <div class="success-icon">

        ✓

    </div>


    <h1>

        Order Placed Successfully!

    </h1>


    <p>

        Thank you for shopping with

        <strong>

            Sulax Shoes Collection

        </strong>.

    </p>


    <p class="order-id">

        Your Order ID is:

        <strong>

            #<?php echo $order_id; ?>

        </strong>

    </p>


    <p>

        Total Amount:

        <strong>

            Rs. <?php echo number_format($total_amount, 2); ?>

        </strong>

    </p>


    <p>

        Payment Method:

        <strong>

            <?php echo htmlspecialchars($payment); ?>

        </strong>

    </p>


    <a href="my-orders.php">

        View My Orders

    </a>


    <a href="index.php">

        Continue Shopping

    </a>


</div>


<script>

    // Clear shopping cart after successful order

    localStorage.removeItem("sulaxCart");

</script>


</body>

</html>