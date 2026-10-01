<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>

    <title>Shopping Cart - Sulax Shoes</title>

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
            float: right;
            margin-left: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .cart-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr 150px 120px 100px;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #ddd;
        }

        .cart-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
        }

        .quantity {
            display: flex;
            align-items: center;
        }

        .quantity button {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: #f5f5f5;
            cursor: pointer;
        }

        .quantity span {
            width: 35px;
            text-align: center;
        }

        .remove-btn {
            border: none;
            background: #dc3545;
            color: white;
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
        }

        .summary {
            margin-top: 25px;
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: right;
        }

        .checkout-btn {
            display: inline-block;
            background: #f85606;
            color: white;
            text-decoration: none;
            padding: 13px 25px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .empty {
            text-align: center;
            padding: 60px 20px;
        }

        .shop-btn {
            display: inline-block;
            background: #f85606;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
        }

        @media(max-width: 750px) {

            .cart-item {
                grid-template-columns: 80px 1fr;
            }

            .cart-item > div {
                margin-bottom: 10px;
            }
        }

    </style>

</head>

<body>

<div class="navbar">

    <strong>🛍️ Sulax Shoes Collection</strong>

    <a href="index.php">
        Continue Shopping
    </a>

</div>

<div class="container">

    <h1>🛒 Shopping Cart</h1>

    <div id="cart-container"></div>

    <div id="cart-summary"></div>

</div>


<script src="script.js"></script>

<script>

function displayCart() {

    const cart = getCart();

    const container =
        document.getElementById(
            "cart-container"
        );

    const summary =
        document.getElementById(
            "cart-summary"
        );

    if (cart.length === 0) {

        container.innerHTML = `
            <div class="cart-box empty">

                <h2>Your cart is empty 🛒</h2>

                <p>
                    Add some shoes to your cart.
                </p>

                <a
                    href="index.php"
                    class="shop-btn"
                >
                    Shop Now
                </a>

            </div>
        `;

        summary.innerHTML = "";

        return;
    }

    let subtotal = 0;

    let html = `
        <div class="cart-box">
    `;

    cart.forEach((item, index) => {

        const itemTotal =
            Number(item.price) *
            Number(item.quantity);

        subtotal += itemTotal;

        html += `

            <div class="cart-item">

                <div>
                    ${
                        item.image
                        ?
                        `<img
                            class="cart-image"
                            src="uploads/${escapeHtml(item.image)}"
                        >`
                        :
                        `<div style="
                            font-size:50px;
                            text-align:center;
                        ">👟</div>`
                    }
                </div>

                <div>

                    <h3>
                        ${escapeHtml(item.name)}
                    </h3>

                    <p>
                        Size:
                        <strong>
                            ${escapeHtml(item.size)}
                        </strong>
                    </p>

                    <p>
                        Rs.
                        ${Number(item.price).toFixed(2)}
                    </p>

                </div>

                <div class="quantity">

                    <button
                        onclick="decreaseQuantity(${index})"
                    >
                        −
                    </button>

                    <span>
                        ${item.quantity}
                    </span>

                    <button
                        onclick="increaseQuantity(${index})"
                    >
                        +
                    </button>

                </div>

                <div>

                    <strong>
                        Rs.
                        ${itemTotal.toFixed(2)}
                    </strong>

                </div>

                <div>

                    <button
                        class="remove-btn"
                        onclick="removeFromCart(${index})"
                    >
                        🗑️ Remove
                    </button>

                </div>

            </div>

        `;
    });

    html += `</div>`;

    container.innerHTML = html;

    const delivery = 100;

    const grandTotal =
        subtotal + delivery;

    summary.innerHTML = `

        <div class="summary">

            <h3>
                Subtotal:
                Rs. ${subtotal.toFixed(2)}
            </h3>

            <p>
                Delivery:
                Rs. ${delivery.toFixed(2)}
            </p>

            <h2>
                Grand Total:
                Rs. ${grandTotal.toFixed(2)}
            </h2>

            <a
                href="checkout.php"
                class="checkout-btn"
            >
                Proceed to Checkout →
            </a>

        </div>

    `;
}


function escapeHtml(text) {

    const div = document.createElement("div");

    div.textContent = text;

    return div.innerHTML;
}

displayCart();

</script>

</body>
</html>