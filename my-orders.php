<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, customer_name, total_amount,
            payment_method, order_status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Orders - Sulax Shoes</title>

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
            margin-left: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .order-box {
            background: white;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f85606;
            color: white;
        }

        .status {
            padding: 6px 10px;
            border-radius: 5px;
            background: #fff3cd;
        }

        .view-btn {
            background: #f85606;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .empty {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>🛍️ Sulax Shoes Collection</strong>

    <div>
        <a href="account.php">My Account</a>
        <a href="index.php">Home</a>
    </div>

</div>

<div class="container">

    <h1>📦 My Orders</h1>

    <?php if (isset($_GET["cancel"])): ?>
        <div style="background:<?php echo $_GET["cancel"] === "success" ? "#e8f7e8" : "#ffe8e8"; ?>;color:<?php echo $_GET["cancel"] === "success" ? "#176b2c" : "#a10000"; ?>;padding:12px;border-radius:6px;margin-bottom:15px;">
            <?php echo $_GET["cancel"] === "success" ? "Order cancelled successfully and stock was restored." : "The order could not be cancelled."; ?>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>

    <div class="order-box">

        <table>

            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Payment</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($order = $result->fetch_assoc()): ?>

            <tr>

                <td>
                    #<?php echo $order["id"]; ?>
                </td>

                <td>
                    <?php echo $order["created_at"]; ?>
                </td>

                <td>
                    <?php echo htmlspecialchars(
                        $order["payment_method"]
                    ); ?>
                </td>

                <td>
                    Rs.
                    <?php echo number_format(
                        $order["total_amount"],
                        2
                    ); ?>
                </td>

                <td>
                    <span class="status">
                        <?php echo htmlspecialchars(
                            $order["order_status"]
                        ); ?>
                    </span>
                </td>

                <td>

                    <div class="order-actions">
                        <a
                            class="view-btn"
                            href="order-details.php?id=<?php echo (int)$order["id"]; ?>"
                        >
                            View Details
                        </a>
                        <?php if (in_array($order["order_status"], ["Pending", "Processing"], true)): ?>
                            <form method="post" action="cancel-order.php" onsubmit="return confirm('Cancel this order?');">
                                <input type="hidden" name="order_id" value="<?php echo (int)$order["id"]; ?>">
                                <button type="submit" class="cancel-order-btn">Cancel</button>
                            </form>
                        <?php endif; ?>
                    </div>

                </td>

            </tr>

            <?php endwhile; ?>

        </table>

    </div>

    <?php else: ?>

        <div class="empty">

            <h2>📦 No Orders Yet</h2>

            <p>
                You haven't placed any orders yet.
            </p>

            <a
                class="view-btn"
                href="index.php"
            >
                Start Shopping
            </a>

        </div>

    <?php endif; ?>

</div>

</body>
</html>