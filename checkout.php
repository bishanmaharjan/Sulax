<?php

session_start();

require_once "config/database.php";


// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = $_SESSION["user_id"];


// ==========================================
// GET USER INFORMATION
// ==========================================

$stmt = $conn->prepare(
    "SELECT name, email, phone, address
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Checkout - Sulax Shoes Collection
    </title>

    <link rel="stylesheet"
          href="style.css">

    <style>

        .checkout-container {

            max-width: 1100px;

            margin: 40px auto;

            padding: 20px;

        }


        .checkout-container h1 {

            margin-bottom: 25px;

        }


        .checkout-grid {

            display: grid;

            grid-template-columns:
                1fr 400px;

            gap: 25px;

        }


        .checkout-box {

            background: white;

            padding: 25px;

            border-radius: 8px;

            box-shadow:
                0 3px 12px
                rgba(0,0,0,0.08);

        }


        .checkout-box h2 {

            margin-top: 0;

            margin-bottom: 20px;

        }


        .form-group {

            margin-bottom: 15px;

        }


        .form-group label {

            display: block;

            margin-bottom: 6px;

            font-weight: bold;

        }


        .form-group input,

        .form-group textarea,

        .form-group select {

            width: 100%;

            padding: 11px;

            border: 1px solid #ddd;

            border-radius: 5px;

            box-sizing: border-box;

            font-size: 15px;

        }


        .form-group textarea {

            min-height: 100px;

            resize: vertical;

        }


        .cart-item {

            display: flex;

            gap: 15px;

            padding: 15px 0;

            border-bottom: 1px solid #eee;

        }


        .cart-item img {

            width: 80px;

            height: 80px;

            object-fit: cover;

            border-radius: 5px;

        }


        .cart-item-info {

            flex: 1;

        }


        .cart-item-info h3 {

            margin: 0 0 8px;

            font-size: 16px;

        }


        .cart-item-info p {

            margin: 4px 0;

            color: #666;

        }


        .summary-row {

            display: flex;

            justify-content: space-between;

            padding: 10px 0;

        }


        .summary-total {

            border-top: 1px solid #ddd;

            margin-top: 10px;

            padding-top: 15px;

            font-size: 20px;

            font-weight: bold;

        }


        .place-order-btn {

            width: 100%;

            padding: 14px;

            margin-top: 20px;

            background: #f85606;

            color: white;

            border: none;

            border-radius: 5px;

            font-size: 17px;

            cursor: pointer;

        }


        .place-order-btn:hover {

            background: #d94700;

        }


        .empty-message {

            text-align: center;

            padding: 40px;

        }


        @media (max-width: 768px) {

            .checkout-grid {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<header>

    <div class="navbar">

        <div class="logo">

            <a href="index.php">

                Sulax Shoes

            </a>

        </div>


        <div class="search-box">

            <input
                type="text"
                placeholder="Search shoes..."
            >

        </div>


        <div class="nav-links">

            <a href="index.php">

                Home

            </a>

            <a href="account.php">

                My Account

            </a>

            <a href="cart.php">

                🛒 Cart

            </a>

        </div>

    </div>

</header>



<!-- ==========================================
     CHECKOUT
========================================== -->

<div class="checkout-container">


    <h1>

        Checkout

    </h1>


    <form
        action="place-order.php"
        method="POST"
        id="checkoutForm"
    >


        <!-- Hidden cart data -->

        <input
            type="hidden"
            name="cart"
            id="cart"
        >


        <div class="checkout-grid">


            <!-- =================================
                 CUSTOMER INFORMATION
            ================================== -->

            <div class="checkout-box">

                <h2>

                    Delivery Information

                </h2>


                <div class="form-group">

                    <label>

                        Full Name

                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?php
                        echo htmlspecialchars(
                            $user["name"]
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>

                        Email

                    </label>

                    <input
                        type="email"
                        value="<?php
                        echo htmlspecialchars(
                            $user["email"]
                        );
                        ?>"
                        disabled
                    >

                </div>


                <div class="form-group">

                    <label>

                        Phone Number

                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?php
                        echo htmlspecialchars(
                            $user["phone"]
                        );
                        ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>

                        Delivery Address

                    </label>

                    <textarea
                        name="address"
                        required
                    ><?php
                    echo htmlspecialchars(
                        $user["address"]
                    );
                    ?></textarea>

                </div>


                <div class="form-group">

                    <label>

                        Payment Method

                    </label>

                    <select
                        name="payment"
                        required
                    >

                        <option value="">

                            Select Payment Method

                        </option>

                        <option value="Cash on Delivery">

                            Cash on Delivery

                        </option>

                        <option value="Online Payment">

                            Online Payment (Demo)

                        </option>

                    </select>

                </div>


            </div>



            <!-- =================================
                 ORDER SUMMARY
            ================================== -->

            <div class="checkout-box">

                <h2>

                    Your Order

                </h2>


                <div id="checkoutItems">

                    <!-- JavaScript loads cart -->

                </div>


                <div class="summary-row">

                    <span>

                        Subtotal

                    </span>

                    <strong>

                        Rs.
                        <span id="subtotal">
                            0.00
                        </span>

                    </strong>

                </div>


                <div class="summary-row">

                    <span>

                        Delivery

                    </span>

                    <strong>

                        Rs. 100.00

                    </strong>

                </div>


                <div class="summary-row summary-total">

                    <span>

                        Total

                    </span>

                    <strong>

                        Rs.
                        <span id="grandTotal">
                            0.00
                        </span>

                    </strong>

                </div>


                <button
                    type="submit"
                    class="place-order-btn"
                    id="placeOrderButton"
                >

                    Place Order

                </button>


            </div>


        </div>


    </form>


</div>



<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script>


// ==========================================
// GET CART
// ==========================================

function getCart() {

    return JSON.parse(
        localStorage.getItem("sulaxCart")
    ) || [];

}


// ==========================================
// DISPLAY CHECKOUT CART
// ==========================================

function displayCheckout() {


    const cart = getCart();

    const container =
        document.getElementById(
            "checkoutItems"
        );


    const subtotalElement =
        document.getElementById(
            "subtotal"
        );


    const totalElement =
        document.getElementById(
            "grandTotal"
        );


    const cartInput =
        document.getElementById(
            "cart"
        );


    const button =
        document.getElementById(
            "placeOrderButton"
        );


    // Send cart to PHP

    cartInput.value =
        JSON.stringify(cart);


    // Empty cart

    if (cart.length === 0) {

        container.innerHTML = `

            <div class="empty-message">

                <p>
                    Your cart is empty.
                </p>

                <a href="index.php">
                    Continue Shopping
                </a>

            </div>

        `;

        button.disabled = true;

        return;

    }


    let subtotal = 0;


    let html = "";


    cart.forEach(function(item) {


        const price =
            Number(item.price);


        const quantity =
            Number(item.quantity);


        const itemTotal =
            price * quantity;


        subtotal += itemTotal;


        html += `

            <div class="cart-item">

                <img
                    src="uploads/${escapeHtml(item.image)}"
                    alt="${escapeHtml(item.name)}"
                >

                <div class="cart-item-info">

                    <h3>
                        ${escapeHtml(item.name)}
                    </h3>

                    <p>
                        Size:
                        ${escapeHtml(item.size)}
                    </p>

                    <p>
                        Quantity:
                        ${quantity}
                    </p>

                    <p>
                        Rs.
                        ${price.toFixed(2)}
                    </p>

                </div>

            </div>

        `;

    });


    container.innerHTML = html;


    const grandTotal =
        subtotal + 100;


    subtotalElement.innerText =
        subtotal.toFixed(2);


    totalElement.innerText =
        grandTotal.toFixed(2);

}


// ==========================================
// ESCAPE HTML
// ==========================================

function escapeHtml(text) {

    const div =
        document.createElement("div");

    div.innerText = text;

    return div.innerHTML;

}


// ==========================================
// SUBMIT CHECK
// ==========================================

document
    .getElementById("checkoutForm")
    .addEventListener(
        "submit",
        function(event) {


            const cart =
                getCart();


            if (
                !cart ||
                cart.length === 0
            ) {

                event.preventDefault();

                alert(
                    "Your cart is empty."
                );

                return;

            }


            // Make sure latest cart
            // is sent to PHP

            document.getElementById(
                "cart"
            ).value =
                JSON.stringify(cart);

        }
    );


// ==========================================
// LOAD CHECKOUT
// ==========================================

displayCheckout();

</script>


</body>

</html>