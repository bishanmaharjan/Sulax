<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT id, name, email, phone, address, created_at
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>

    <title>My Account - Sulax Shoes</title>

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
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .profile {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .profile-icon {
            font-size: 60px;
        }

        .profile h2 {
            color: #f85606;
        }

        .menu {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .menu a {
            background: white;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            color: #333;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        .menu a:hover {
            background: #fff3ed;
        }

        .menu-icon {
            font-size: 35px;
            display: block;
            margin-bottom: 10px;
        }

        @media(max-width: 700px) {
            .menu {
                grid-template-columns: 1fr;
            }
        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>🛍️ Sulax Shoes Collection</strong>

    <span style="float:right;">
        <a href="index.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </span>

</div>

<div class="container">

    <div class="profile">

        <div class="profile-icon">👤</div>

        <h2>
            Welcome,
            <?php echo htmlspecialchars($user["name"]); ?>
        </h2>

        <p>
            <strong>Email:</strong>
            <?php echo htmlspecialchars($user["email"]); ?>
        </p>

        <p>
            <strong>Phone:</strong>
            <?php echo htmlspecialchars($user["phone"]); ?>
        </p>

        <p>
            <strong>Address:</strong>
            <?php echo nl2br(
                htmlspecialchars($user["address"])
            ); ?>
        </p>

    </div>

    <div class="menu">

        <a href="my-orders.php">
            <span class="menu-icon">📦</span>
            My Orders
        </a>

        <a href="update-profile.php">
            <span class="menu-icon">✏️</span>
            Edit Profile
        </a>

        <a href="change-password.php">
            <span class="menu-icon">🔐</span>
            Change Password
        </a>

        <a href="cart.php">
            <span class="menu-icon">🛒</span>
            My Cart
        </a>

        <a href="index.php">
            <span class="menu-icon">🏠</span>
            Continue Shopping
        </a>

        <a href="logout.php">
            <span class="menu-icon">🚪</span>
            Logout
        </a>

    </div>

</div>

</body>
</html>