<?php

session_start();

require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$message = "";

$stmt = $conn->prepare(
    "SELECT name, email, phone, address
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    $stmt = $conn->prepare(
        "UPDATE users
         SET name = ?, phone = ?, address = ?
         WHERE id = ?"
    );

    $stmt->bind_param(
        "sssi",
        $name,
        $phone,
        $address,
        $user_id
    );

    if ($stmt->execute()) {

        $_SESSION["user_name"] = $name;

        $message = "Profile updated successfully!";

        $user["name"] = $name;
        $user["phone"] = $phone;
        $user["address"] = $address;
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Profile - Sulax Shoes</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
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

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin-top: 7px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 20px;
            background: #f85606;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .success {
            color: green;
            background: #e8f7e8;
            padding: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>✏️ Edit Profile</h1>

    <?php if ($message): ?>

        <div class="success">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <label>Name</label>

        <input
            type="text"
            name="name"
            value="<?php echo htmlspecialchars($user["name"]); ?>"
            required
        >

        <label>Email</label>

        <input
            type="email"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
            disabled
        >

        <label>Phone</label>

        <input
            type="text"
            name="phone"
            value="<?php echo htmlspecialchars($user["phone"]); ?>"
        >

        <label>Address</label>

        <textarea name="address"><?php
            echo htmlspecialchars($user["address"]);
        ?></textarea>

        <button type="submit">
            Update Profile
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