<?php
session_start();
require_once "config/database.php";

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");
$categories = [];
$category_result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row["category"];
    }
}

$sql = "SELECT id, name, category, price, old_price, discount, image, stock, rating
        FROM products";
$conditions = [];
if ($search !== "") {
    $conditions[] = "(name LIKE ? OR category LIKE ?)";
}
if ($category !== "") {
    $conditions[] = "category = ?";
}
if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY created_at DESC LIMIT 24";

$products_stmt = $conn->prepare($sql);
if ($search !== "" && $category !== "") {
    $search_term = "%" . $search . "%";
    $products_stmt->bind_param("sss", $search_term, $search_term, $category);
} elseif ($search !== "") {
    $search_term = "%" . $search . "%";
    $products_stmt->bind_param("ss", $search_term, $search_term);
} elseif ($category !== "") {
    $products_stmt->bind_param("s", $category);
}
$products_stmt->execute();
$products = $products_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sulax Shoes Collection</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-bar">
    <div class="top-container">
        <span>Welcome to Sulax Shoes Collection</span>
        <div class="top-links">
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="my-orders.php">My Orders</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <span aria-hidden="true" style="font-size:38px">👟</span>
            <div><h1>SULAX</h1><span>SHOES COLLECTION</span></div>
        </a>
        <form class="search-box" action="index.php" method="get">
            <?php if ($category !== ""): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>"><?php endif; ?>
            <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search shoes and styles..." aria-label="Search products">
            <button type="submit" aria-label="Search">⌕</button>
        </form>
        <nav class="nav-icons" aria-label="Account and shopping">
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="account.php" class="nav-icon">👤<span>Account</span></a>
                <a href="wishlist.php" class="nav-icon">♡<span>Wishlist</span></a>
            <?php endif; ?>
            <a href="cart.php" class="nav-icon cart-icon" aria-label="Shopping cart">🛒<span class="cart-count" id="cartCount">0</span></a>
        </nav>
    </div>
</header>

<nav class="category-nav" aria-label="Shop by category">
    <div class="category-container">
        <a href="index.php">All Shoes</a>
        <?php foreach ($categories as $category_name): ?>
            <a href="index.php?category=<?php echo rawurlencode($category_name); ?>"<?php echo $category === $category_name ? ' aria-current="page"' : ""; ?>><?php echo htmlspecialchars($category_name); ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<main>
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <p class="hero-small">SULAX SHOES COLLECTION</p>
                <h2>Find your next everyday pair.</h2>
                <p>Comfortable fits, fresh styles, and a little more confidence in every step.</p>
                <a class="shop-btn" href="#products">Shop the collection <span aria-hidden="true">→</span></a>
            </div>
            <div class="hero-shoe" aria-hidden="true"><div class="shoe-circle">👟</div></div>
        </div>
    </section>

    <section class="section products-section" id="products">
        <div class="section-title product-heading">
            <div>
                <h2><?php echo $category !== "" ? htmlspecialchars($category) : ($search !== "" ? "Search results" : "Shop all shoes"); ?></h2>
                <p><?php echo $products->num_rows; ?> styles to explore</p>
            </div>
            <?php if ($category !== "" || $search !== ""): ?><a href="index.php">Clear filters</a><?php endif; ?>
        </div>
        <?php if ($products->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($product = $products->fetch_assoc()): ?>
                    <article class="product-card">
                        <a class="product-image" href="product-details.php?id=<?php echo (int)$product["id"]; ?>" aria-label="View <?php echo htmlspecialchars($product["name"]); ?>">
                            <?php if ((int)$product["discount"] > 0): ?><span class="discount"><?php echo (int)$product["discount"]; ?>% OFF</span><?php endif; ?>
                            <?php if (!empty($product["image"])): ?>
                                <img src="uploads/<?php echo rawurlencode(basename($product["image"])); ?>" alt="<?php echo htmlspecialchars($product["name"]); ?>">
                            <?php else: ?>
                                <span class="shoe-placeholder" aria-hidden="true">👟</span>
                            <?php endif; ?>
                        </a>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($product["category"]); ?></p>
                            <h3><a href="product-details.php?id=<?php echo (int)$product["id"]; ?>"><?php echo htmlspecialchars($product["name"]); ?></a></h3>
                            <p class="rating" aria-label="Rating <?php echo number_format((float)$product["rating"], 1); ?> out of 5">★ <?php echo number_format((float)$product["rating"], 1); ?><span> / 5</span></p>
                            <p class="price">Rs. <?php echo number_format((float)$product["price"], 2); ?></p>
                            <?php if (!empty($product["old_price"])): ?><p class="old-price">Rs. <?php echo number_format((float)$product["old_price"], 2); ?></p><?php endif; ?>
                            <a class="add-cart" href="product-details.php?id=<?php echo (int)$product["id"]; ?>">View details</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-products"><p>No shoes match those filters.</p><a href="index.php">Browse all shoes</a></div>
        <?php endif; ?>
    </section>
</main>

<footer class="site-footer"><p>&copy; <?php echo date("Y"); ?> Sulax Shoes Collection <span aria-hidden="true">|</span> <a href="admin/login.php">Admin Login</a></p></footer>
<script src="script.js"></script>
</body>
</html>