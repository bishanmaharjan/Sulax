<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($name === "" || strlen($name) < 2) {
        $message = "Please enter a valid name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif ($phone === "" || !preg_match("/^\+?[0-9]{7,15}$/", preg_replace('/[\s-]/', '', $phone))) {
        $message = "Please enter a valid phone number.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email is already registered.";

        } else {

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (name,email,phone,address,password)
                VALUES (?,?,?,?,?)"
            );

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $phone,
                $address,
                $hashed_password
            );

            if ($stmt->execute()) {

                header(
                    "Location: login.php?registered=1"
                );

                exit();

            } else {

                $message = "Registration failed.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Register - Sulax Shoes</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <div class="auth-logo">

            <h1>SULAX</h1>

            <p>SHOES COLLECTION</p>

        </div>

        <h2>Create Account</h2>

        <?php if ($message): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Full Name</label>

            <input
                type="text"
                name="name"
                required
                placeholder="Enter your name"
            >

            <label>Email</label>

            <input
                type="email"
                name="email"
                required
                placeholder="Enter your email"
            >

            <label>Phone Number</label>

            <input
                type="text"
                name="phone"
                required
                placeholder="98XXXXXXXX"
            >

            <label>Address</label>

            <textarea
                name="address"
                required
                placeholder="Enter your address"
            ></textarea>

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                placeholder="Create password"
            >

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                required
                placeholder="Confirm password"
            >

            <button type="submit"
                    class="auth-button">

                Create Account

            </button>

        </form>

        <p class="auth-bottom">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</div>

</body>
</html>