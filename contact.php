<?php
session_start();
require_once "config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $body = trim($_POST["message"] ?? "");

    if ($name === "" || mb_strlen($name) < 2) {
        $error = "Please enter your name.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($subject === "") {
        $error = "Please enter a subject.";
    } elseif ($body === "") {
        $error = "Please enter your message.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $subject, $body);
        if ($stmt->execute()) {
            $message = "Your message has been sent successfully. We will contact you soon.";
        } else {
            $error = "Unable to send your message right now.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - Sulax Shoes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar"><div class="top-container"><div>Welcome to Sulax Shoes Collection</div><div class="top-links"><a href="index.php">Shop</a><a href="cart.php">Cart</a></div></div></div>
<header class="navbar"><div class="nav-container">
<a href="index.php" class="logo"><strong>SULAX</strong><span>SHOES COLLECTION</span></a>
<form class="search-box" action="index.php" method="get"><input type="text" name="search" placeholder="Search for shoes..."><button type="submit" aria-label="Search">🔍</button></form>
<div class="nav-right">
<?php if (isset($_SESSION["user_id"])): ?><a href="account.php" class="nav-icon">👤<span>Account</span></a><?php else: ?><a href="login.php" class="nav-icon">👤<span>Login</span></a><?php endif; ?>
<a href="wishlist.php" class="nav-icon">❤️<span>Wishlist</span></a><a href="cart.php" class="cart-icon">🛒 <b id="cartCount">0</b></a>
</div></div></header>
<main class="contact-container">
<div class="contact-grid">
<div class="contact-card"><h1>Contact Us</h1><p>Have a question about our shoes, delivery or orders? Send us a message.</p>
<div class="contact-detail"><i class="fa-solid fa-location-dot"></i><div><strong>Address</strong><br>Kathmandu, Nepal</div></div>
<div class="contact-detail"><i class="fa-solid fa-phone"></i><div><strong>Phone</strong><br>+977-98XXXXXXXX</div></div>
<div class="contact-detail"><i class="fa-solid fa-envelope"></i><div><strong>Email</strong><br>info@sulaxshoes.com</div></div>
</div>
<div class="contact-card"><h2>Send a Message</h2>
<?php if ($message): ?><div class="review-message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="review-message review-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form class="contact-form" method="post">
<label>Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($_POST["name"] ?? ""); ?>" required>
<label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($_POST["email"] ?? ($_SESSION["user_email"] ?? "")); ?>" required>
<label>Subject</label><input type="text" name="subject" value="<?php echo htmlspecialchars($_POST["subject"] ?? ""); ?>" required>
<label>Message</label><textarea name="message" required><?php echo htmlspecialchars($_POST["message"] ?? ""); ?></textarea>
<button type="submit">Send Message</button>
</form></div></div></main>
<footer><div class="copyright">© <?php echo date("Y"); ?> Sulax Shoes Collection. All Rights Reserved.</div></footer>
<script src="script.js"></script>
</body></html>
