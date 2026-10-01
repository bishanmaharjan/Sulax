<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$order_id = intval($_GET["id"] ?? 0);

$stmt = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    die("Order not found.");
}

$order = $order_result->fetch_assoc();

$stmt = $conn->prepare(
    "SELECT *
     FROM order_items
     WHERE order_id = ?"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$items = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>Order Details - Sulax Shoes</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: #f85606;
            color: white;
            padding: 18px 30px;
        }

        .navbar a {
            float: right;
            color: white;
            text-decoration: none;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #f85606;
            color: white;
            text-align: left;
        }

        .status {
            display: inline-block;
            padding: 8px 15px;
            background: #fff3cd;
            border-radius: 5px;
        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>🛍️ Sulax Shoes Collection</strong>

    <a href="my-orders.php">
        ← My Orders
    </a>

</div>

<div class="container">

    <h1>Order #<?php echo $order["id"]; ?></h1>

    <div class="box">

        <h2>Order Information</h2>

        <p>
            <strong>Customer:</strong>
            <?php echo htmlspecialchars(
                $order["customer_name"]
            ); ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?php echo htmlspecialchars(
                $order["phone"]
            ); ?>
        </p>

        <p>
            <strong>Address:</strong>
            <?php echo nl2br(
                htmlspecialchars($order["address"])
            ); ?>
        </p>

        <p>
            <strong>Payment:</strong>
            <?php echo htmlspecialchars(
                $order["payment_method"]
            ); ?>
        </p>

        <p>
            <strong>Status:</strong>

            <span class="status">
                <?php echo htmlspecialchars(
                    $order["order_status"]
                ); ?>
            </span>

        </p>

        <p>
            <strong>Order Date:</strong>
            <?php echo $order["created_at"]; ?>
        </p>

    </div>

    <div class="box">

        <h2>🛒 Ordered Products</h2>

        <table>

            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>

            <?php while ($item = $items->fetch_assoc()): ?>

            <tr>

                <td>
                    <?php echo htmlspecialchars(
                        $item["product_name"]
                    ); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars(
                        $item["size"]
                    ); ?>
                </td>

                <td>
                    Rs.
                    <?php echo number_format(
                        $item["price"],
                        2
                    ); ?>
                </td>

                <td>
                    <?php echo $item["quantity"]; ?>
                </td>

                <td>
                    Rs.
                    <?php echo number_format(
                        $item["price"] * $item["quantity"],
                        2
                    ); ?>
                </td>

            </tr>

            <?php endwhile; ?>

        </table>

        <h2 style="text-align:right;">
            Total:
            Rs.
            <?php echo number_format(
                $order["total_amount"],
                2
            ); ?>
        </h2>

    </div>

</div>

</body>
</html>