// ==========================================
// SULAX SHOES - SHARED CART FUNCTIONS
// ==========================================

function getCart() {
    try {
        const data = JSON.parse(localStorage.getItem("sulaxCart"));
        return Array.isArray(data) ? data : [];
    } catch (error) {
        localStorage.removeItem("sulaxCart");
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem("sulaxCart", JSON.stringify(cart));
    updateCartCount();
}

function addToCart(product) {
    const cart = getCart();

    const productId = Number(product.id);
    const size = String(product.size ?? "");
    const quantity = Math.max(1, Math.floor(Number(product.quantity) || 1));
    const stock = Number(product.stock ?? 0);

    if (!productId || !size) {
        alert("Please select a shoe size.");
        return;
    }

    if (stock <= 0) {
        alert("This product is out of stock.");
        return;
    }

    const existingIndex = cart.findIndex(
        item => Number(item.id) === productId &&
                String(item.size) === size
    );

    if (existingIndex !== -1) {
        const newQuantity =
            (Number(cart[existingIndex].quantity) || 0) + quantity;

        if (newQuantity > stock) {
            alert("Only " + stock + " items are available.");
            return;
        }

        cart[existingIndex].quantity = newQuantity;
    } else {
        if (quantity > stock) {
            alert("Only " + stock + " items are available.");
            return;
        }

        cart.push({
            id: productId,
            name: String(product.name || ""),
            price: Number(product.price) || 0,
            image: String(product.image || ""),
            size: size,
            quantity: quantity
        });
    }

    saveCart(cart);
    alert("Product added to cart!");
}

function updateCartCount() {
    const cart = getCart();
    const totalQuantity = cart.reduce(
        (total, item) => total + (Number(item.quantity) || 0),
        0
    );

    const counters = document.querySelectorAll(
        "#cartCount, #cart-count"
    );

    counters.forEach(counter => {
        counter.textContent = totalQuantity;
    });
}

function removeFromCart(index) {
    const cart = getCart();

    if (index < 0 || index >= cart.length) return;

    cart.splice(index, 1);
    saveCart(cart);
    location.reload();
}

function increaseQuantity(index) {
    const cart = getCart();

    if (!cart[index]) return;

    cart[index].quantity =
        Math.max(1, Math.floor(Number(cart[index].quantity) || 0) + 1);

    saveCart(cart);
    location.reload();
}

function decreaseQuantity(index) {
    const cart = getCart();

    if (!cart[index]) return;

    const quantity = Number(cart[index].quantity) || 1;

    if (quantity > 1) {
        cart[index].quantity = Math.max(1, Math.floor(quantity) - 1);
    } else {
        cart.splice(index, 1);
    }

    saveCart(cart);
    location.reload();
}

function escapeHtml(value) {
    const div = document.createElement("div");
    div.textContent = value ?? "";
    return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", updateCartCount);
