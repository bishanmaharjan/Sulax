<?php
session_start();
require_once "config/database.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    die("Product not found.");
}

$related_stmt = $conn->prepare(
    "SELECT id, name, price, old_price, discount, image, stock, rating
     FROM products
     WHERE category = ? AND id <> ?
     ORDER BY created_at DESC
     LIMIT 4"
);
$related_stmt->bind_param("si", $product["category"], $id);
$related_stmt->execute();
$related = $related_stmt->get_result();

$rating = max(0, min(5, (float)$product["rating"]));
$stock = max(0, (int)$product["stock"]);

$review_stmt = $conn->prepare(
    "SELECT r.rating, r.comment, r.created_at, u.name
     FROM reviews r
     INNER JOIN users u ON u.id = r.user_id
     WHERE r.product_id = ?
     ORDER BY r.id DESC"
);
$review_stmt->bind_param("i", $id);
$review_stmt->execute();
$reviews = $review_stmt->get_result();

$can_review = false;
$existing_review = false;
if (isset($_SESSION["user_id"])) {
    $uid = (int)$_SESSION["user_id"];
    $purchase_stmt = $conn->prepare(
        "SELECT oi.order_id FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE oi.product_id = ? AND o.user_id = ? AND o.order_status = 'Delivered'
         LIMIT 1"
    );
    $purchase_stmt->bind_param("ii", $id, $uid);
    $purchase_stmt->execute();
    $can_review = (bool)$purchase_stmt->get_result()->fetch_assoc();
    $purchase_stmt->close();

    $existing_stmt = $conn->prepare("SELECT id, rating, comment FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
    $existing_stmt->bind_param("ii", $uid, $id);
    $existing_stmt->execute();
    $existing_review = $existing_stmt->get_result()->fetch_assoc();
    $existing_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($product["name"]); ?> - Sulax Shoes</title>
<link rel="stylesheet" href="style.css">
<style>
.details-wrap{max-width:1200px;margin:35px auto;padding:0 20px}
.product-main{background:#fff;border-radius:14px;padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:40px;box-shadow:0 3px 15px rgba(0,0,0,.08)}
.gallery{display:flex;flex-direction:column;gap:12px}
.main-image{height:470px;background:#f8f8f8;border-radius:12px;display:flex;align-items:center;justify-content:center;overflow:hidden}
.main-image img{width:100%;height:100%;object-fit:contain}
.image-placeholder{font-size:110px;color:#aaa}
.thumbs{display:flex;gap:10px}
.thumb{width:78px;height:70px;border:2px solid #eee;border-radius:8px;background:#fff;overflow:hidden;cursor:pointer}
.thumb img{width:100%;height:100%;object-fit:cover}
.info h1{font-size:32px;margin:0 0 12px}
.meta{display:flex;gap:15px;flex-wrap:wrap;color:#777;font-size:14px}
.rating-stars{color:#f85606;margin:15px 0}
.price-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:12px 0}
.current-price{font-size:30px;font-weight:700;color:#f85606}
.old-price{text-decoration:line-through;color:#999}
.discount-badge{background:#e8f7e8;color:#198754;padding:5px 9px;border-radius:5px;font-weight:bold}
.stock-good{color:#198754;font-weight:bold}.stock-out{color:#dc3545;font-weight:bold}
.section{margin-top:22px}.section h3{margin-bottom:12px}
.sizes{display:flex;gap:9px;flex-wrap:wrap}.size-btn{width:48px;height:42px;background:#fff;border:1px solid #ccc;border-radius:6px;cursor:pointer}.size-btn:hover,.size-btn.selected{background:#f85606;color:#fff;border-color:#f85606}
.qty-box{display:flex;align-items:center;border:1px solid #ddd;width:max-content;border-radius:6px;overflow:hidden}.qty-box button{width:42px;height:42px;border:0;background:#f5f5f5;cursor:pointer;font-size:20px}.qty-box input{width:50px;height:42px;border:0;text-align:center;font-size:16px}
.actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:25px}.actions button,.wishlist-btn{padding:13px 22px;border-radius:6px;font-size:16px;cursor:pointer;text-decoration:none}.buy-now{border:1px solid #f85606;background:#fff;color:#f85606}.add-cart{border:1px solid #f85606;background:#f85606;color:#fff}.actions button:disabled{opacity:.5;cursor:not-allowed}.wishlist-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid #f85606;background:#fff;color:#f85606}.description{margin-top:28px;padding-top:22px;border-top:1px solid #eee;line-height:1.7}.related{margin:35px 0}.related h2{margin-bottom:18px}.related-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.related-card{background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.07)}.related-card img{width:100%;height:200px;object-fit:cover}.related-card .rc-info{padding:14px}.related-card h3{font-size:16px;margin:0 0 8px}.related-card a{color:#f85606;text-decoration:none;font-weight:bold}.back-link{display:inline-block;margin-bottom:15px;color:#f85606;text-decoration:none}
@media(max-width:900px){.product-main{grid-template-columns:1fr}.main-image{height:400px}.related-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.details-wrap{padding:0 12px}.product-main{padding:18px}.info h1{font-size:25px}.main-image{height:300px}.related-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="top-bar">
    <div class="top-container">
        <div>Welcome to Sulax Shoes Collection</div>
        <div class="top-links">
            <a href="index.php">Shop</a>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="my-orders.php">My Orders</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-shoe-prints">👟</i>
            <div><h1>SULAX</h1><span>SHOES COLLECTION</span></div>
        </a>
        <form class="search-box" action="index.php" method="get">
            <input type="text" name="search" placeholder="Search for shoes...">
            <button type="submit">🔍</button>
        </form>
        <div class="nav-icons">
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="account.php" class="nav-icon">👤<span>Account</span></a>
                <a href="wishlist.php" class="nav-icon">❤️<span>Wishlist</span></a>
            <?php else: ?>
                <a href="login.php" class="nav-icon">👤<span>Login</span></a>
                <a href="register.php" class="nav-icon">📝<span>Register</span></a>
            <?php endif; ?>
            <a href="cart.php" class="cart-icon">🛒<span class="cart-count" id="cartCount">0</span></a>
        </div>
    </div>
</header>

<main class="details-wrap">
    <a href="index.php" class="back-link">← Back to Products</a>

    <section class="product-main">
        <div class="gallery">
            <div class="main-image" id="mainImageBox">
                <?php if (!empty($product["image"])): ?>
                    <img id="mainProductImage" src="uploads/<?php echo rawurlencode($product["image"]); ?>" alt="<?php echo htmlspecialchars($product["name"]); ?>">
                <?php else: ?>
                    <div class="image-placeholder">👟</div>
                <?php endif; ?>
            </div>
            <?php if (!empty($product["image"])): ?>
            <div class="thumbs">
                <button type="button" class="thumb" onclick="changeMainImage(this)">
                    <img src="uploads/<?php echo rawurlencode($product["image"]); ?>" alt="Product thumbnail">
                </button>
            </div>
            <?php endif; ?>
        </div>

        <div class="info">
            <h1><?php echo htmlspecialchars($product["name"]); ?></h1>
            <div class="meta">
                <span>Category: <?php echo htmlspecialchars($product["category"]); ?></span>
                <span>Product #<?php echo (int)$product["id"]; ?></span>
            </div>

            <div class="rating-stars" aria-label="Rating <?php echo $rating; ?> out of 5">
                <?php for ($i=1;$i<=5;$i++): ?>
                    <?php echo $i <= round($rating) ? "★" : "☆"; ?>
                <?php endfor; ?>
                <span style="color:#666;margin-left:7px"><?php echo number_format($rating,1); ?>/5</span>
            </div>

            <div class="price-row">
                <span class="current-price">Rs. <?php echo number_format((float)$product["price"],2); ?></span>
                <?php if ((float)$product["old_price"] > (float)$product["price"]): ?>
                    <span class="old-price">Rs. <?php echo number_format((float)$product["old_price"],2); ?></span>
                <?php endif; ?>
                <?php if ((int)$product["discount"] > 0): ?>
                    <span class="discount-badge">-<?php echo (int)$product["discount"]; ?>%</span>
                <?php endif; ?>
            </div>

            <div class="section">
                <strong class="<?php echo $stock > 0 ? "stock-good" : "stock-out"; ?>">
                    <?php echo $stock > 0 ? "✓ In Stock — $stock available" : "✕ Out of Stock"; ?>
                </strong>
            </div>

            <div class="section">
                <h3>Select Size</h3>
                <div class="sizes">
                    <?php for ($size=6;$size<=11;$size++): ?>
                        <button type="button" class="size-btn" onclick="selectSize(this)"><?php echo $size; ?></button>
                    <?php endfor; ?>
                </div>
                <small id="sizeError" style="display:block;color:#dc3545;margin-top:8px"></small>
            </div>

            <div class="section">
                <h3>Quantity</h3>
                <div class="qty-box">
                    <button type="button" onclick="changeQuantity(-1)">−</button>
                    <input id="quantity" type="number" value="1" min="1" max="<?php echo max(1,$stock); ?>" readonly>
                    <button type="button" onclick="changeQuantity(1)">+</button>
                </div>
            </div>

            <div class="actions">
                <button type="button" class="buy-now" onclick="buyNow()" <?php echo $stock <= 0 ? "disabled" : ""; ?>>⚡ Buy Now</button>
                <button type="button" class="add-cart" onclick="addProductToCart()" <?php echo $stock <= 0 ? "disabled" : ""; ?>>🛒 <?php echo $stock > 0 ? "Add to Cart" : "Out of Stock"; ?></button>
                <a class="wishlist-btn" href="add-wishlist.php?id=<?php echo (int)$product["id"]; ?>&redirect=product">❤️ Wishlist</a>
            </div>

            <div class="description">
                <h2>Product Description</h2>
                <p><?php echo nl2br(htmlspecialchars($product["description"] ?? "No description available.")); ?></p>
            </div>
        </div>
    </section>

    <?php if ($related && $related->num_rows > 0): ?>
    <section class="related">
        <h2>Related Products</h2>
        <div class="related-grid">
            <?php while ($item = $related->fetch_assoc()): ?>
            <article class="related-card">
                <?php if (!empty($item["image"])): ?>
                    <img src="uploads/<?php echo rawurlencode($item["image"]); ?>" alt="<?php echo htmlspecialchars($item["name"]); ?>">
                <?php else: ?>
                    <div style="height:200px;display:flex;align-items:center;justify-content:center;font-size:70px;background:#f8f8f8">👟</div>
                <?php endif; ?>
                <div class="rc-info">
                    <h3><?php echo htmlspecialchars($item["name"]); ?></h3>
                    <div style="color:#f85606">⭐ <?php echo number_format((float)$item["rating"],1); ?></div>
                    <p style="font-weight:bold">Rs. <?php echo number_format((float)$item["price"],2); ?></p>
                    <a href="product-details.php?id=<?php echo (int)$item["id"]; ?>">View Product →</a>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<section class="reviews-section" id="reviews">
    <div class="review-layout">
        <div class="review-box">
            <h2><?php echo $existing_review ? "Update Your Review" : "Write a Review"; ?></h2>
            <?php if (isset($_GET["review"])): ?><div class="review-message">Review saved successfully.</div><?php endif; ?>
            <?php if (isset($_GET["review_error"])): ?><div class="review-message review-error">
                <?php echo $_GET["review_error"] === "purchase" ? "You can review this product after a delivered order." : "Unable to save your review."; ?>
            </div><?php endif; ?>
            <?php if (!isset($_SESSION["user_id"])): ?>
                <p>Please <a href="login.php" style="color:#f85606;font-weight:600">login</a> to review this product.</p>
            <?php elseif (!$can_review): ?>
                <p>You can submit a review after you purchase and receive this product.</p>
            <?php else: ?>
                <form class="review-form" method="post" action="review-submit.php">
                    <input type="hidden" name="product_id" value="<?php echo (int)$id; ?>">
                    <label for="review-rating">Rating</label>
                    <select id="review-rating" name="rating" required>
                        <?php $selected_rating = (int)($existing_review["rating"] ?? 5); ?>
                        <?php for ($r=5;$r>=1;$r--): ?>
                            <option value="<?php echo $r; ?>" <?php echo $selected_rating === $r ? "selected" : ""; ?>><?php echo $r; ?> Star<?php echo $r>1?'s':''; ?></option>
                        <?php endfor; ?>
                    </select>
                    <label for="review-comment">Comment</label>
                    <textarea id="review-comment" name="comment" maxlength="1000" placeholder="Share your experience..."><?php echo htmlspecialchars($existing_review["comment"] ?? ""); ?></textarea>
                    <button class="review-submit" type="submit"><?php echo $existing_review ? "Update Review" : "Submit Review"; ?></button>
                </form>
            <?php endif; ?>
        </div>
        <div class="review-list-box">
            <h2>Customer Reviews</h2>
            <?php if ($reviews && $reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-head">
                            <strong><?php echo htmlspecialchars($review["name"]); ?></strong>
                            <span class="review-date"><?php echo htmlspecialchars(date("d M Y", strtotime($review["created_at"]))); ?></span>
                        </div>
                        <div class="review-stars"><?php echo str_repeat("★", (int)$review["rating"]) . str_repeat("☆", 5-(int)$review["rating"]); ?></div>
                        <?php if (trim((string)$review["comment"]) !== ""): ?><p class="review-comment"><?php echo htmlspecialchars($review["comment"]); ?></p><?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-reviews">No reviews yet. Be the first verified customer to review this product.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer style="background:#222;color:#fff;text-align:center;padding:25px;margin-top:20px">
    © <?php echo date("Y"); ?> Sulax Shoes Collection. All rights reserved.
</footer>

<script src="script.js"></script>
<script>
const product = {
    id: <?php echo (int)$product["id"]; ?>,
    name: <?php echo json_encode($product["name"]); ?>,
    price: <?php echo json_encode((float)$product["price"]); ?>,
    image: <?php echo json_encode($product["image"] ?? ""); ?>,
    stock: <?php echo $stock; ?>
};
let selectedSize = null;

function selectSize(button) {
    document.querySelectorAll(".size-btn").forEach(btn => btn.classList.remove("selected"));
    button.classList.add("selected");
    selectedSize = button.textContent.trim();
    document.getElementById("sizeError").textContent = "";
}

function changeQuantity(amount) {
    const input = document.getElementById("quantity");
    const max = parseInt(input.max, 10);
    let quantity = parseInt(input.value, 10) + amount;
    quantity = Math.max(1, Math.min(max, quantity));
    input.value = quantity;
}

function getCartSafe() {
    try { return JSON.parse(localStorage.getItem("sulaxCart")) || []; }
    catch (e) { localStorage.removeItem("sulaxCart"); return []; }
}

function saveCartSafe(cart) {
    localStorage.setItem("sulaxCart", JSON.stringify(cart));
    if (typeof updateCartCount === "function") updateCartCount();
}

function addProductToCart() {
    if (!selectedSize) {
        document.getElementById("sizeError").textContent = "Please select a shoe size.";
        return false;
    }
    if (product.stock <= 0) {
        alert("Sorry, this product is out of stock.");
        return false;
    }

    const quantity = parseInt(document.getElementById("quantity").value, 10);
    const cart = getCartSafe();
    const index = cart.findIndex(item => item.id == product.id && String(item.size) === String(selectedSize));

    if (index >= 0) {
        const newQuantity = Number(cart[index].quantity) + quantity;
        if (newQuantity > product.stock) {
            alert("Only " + product.stock + " items are available.");
            return false;
        }
        cart[index].quantity = newQuantity;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: Number(product.price),
            image: product.image,
            size: selectedSize,
            quantity: quantity
        });
    }

    saveCartSafe(cart);
    alert(product.name + " added to cart!");
    return true;
}

function buyNow() {
    if (addProductToCart()) window.location.href = "cart.php";
}

function changeMainImage(button) {
    const image = button.querySelector("img");
    const main = document.getElementById("mainProductImage");
    if (image && main) main.src = image.src;
}
</script>
</body>
</html>
