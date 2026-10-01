<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {

        $stmt = $conn->prepare(
            "SELECT password
             FROM users
             WHERE id = ?"
        );

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($current_password, $user["password"])) {

            $new_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "UPDATE users
                 SET password = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "si",
                $new_hash,
                $user_id
            );

            $stmt->execute();

            $message = "Password changed successfully!";

        } else {

            $error = "Current password is incorrect.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Change Password - Sulax Shoes</title>

    <style>

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .container {
            max-width: 500px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        h1 {
            color: #f85606;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 20px;
            background: #f85606;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .success {
            background: #e8f7e8;
            color: green;
            padding: 10px;
        }

        .error {
            background: #ffe8e8;
            color: red;
            padding: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>🔐 Change Password</h1>

    <?php if ($message): ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Current Password</label>

        <input
            type="password"
            name="current_password"
            required
        >

        <label>New Password</label>

        <input
            type="password"
            name="new_password"
            minlength="6"
            required
        >

        <label>Confirm New Password</label>

        <input
            type="password"
            name="confirm_password"
            minlength="6"
            required
        >

        <button type="submit">
            Change Password
        </button>

    </form>

    <p style="text-align:center;">
        <a href="account.php">
            ← Back to My Account
        </a>
    </p>

</div>

</body>
</html>