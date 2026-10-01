<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email = ?"
    );

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (
            password_verify(
                $password,
                $user["password"]
            )
        ) {

            session_regenerate_id(true);

            $_SESSION["user_id"] =
                $user["id"];

            $_SESSION["user_name"] =
                $user["name"];

            $_SESSION["user_email"] =
                $user["email"];

            header("Location: index.php");

            exit();

        } else {

            $message = "Invalid email or password.";

        }

    } else {

        $message = "Invalid email or password.";

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Login - Sulax Shoes</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <div class="auth-logo">

            <h1>SULAX</h1>

            <p>SHOES COLLECTION</p>

        </div>

        <h2>Welcome Back</h2>

        <?php if (isset($_GET["registered"])): ?>

            <div class="success-message">

                Registration successful.
                Please login.

            </div>

        <?php endif; ?>

        <?php if ($message): ?>

            <div class="error-message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <label>Email</label>

            <input
                type="email"
                name="email"
                required
                placeholder="Enter your email"
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                required
                placeholder="Enter your password"
            >

            <button
                type="submit"
                class="auth-button"
            >

                Login

            </button>

        </form>

        <p class="auth-bottom">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </p>

    </div>

</div>

</body>
</html>