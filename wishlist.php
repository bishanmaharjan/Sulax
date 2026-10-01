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
// REMOVE PRODUCT
// ==========================================

if (isset($_GET["remove"])) {

    $product_id =
        intval($_GET["remove"]);


    $stmt = $conn->prepare(
        "DELETE FROM wishlist
         WHERE user_id = ?
         AND product_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $user_id,
        $product_id
    );


    $stmt->execute();

    $stmt->close();


    header("Location: wishlist.php");

    exit();

}


// ==========================================
// GET WISHLIST PRODUCTS
// ==========================================

$stmt = $conn->prepare(

    "SELECT
        wishlist.id AS wishlist_id,
        products.id,
        products.name,
        products.category,
        products.price,
        products.old_price,
        products.discount,
        products.image,
        products.stock,
        products.rating

     FROM wishlist

     INNER JOIN products
     ON wishlist.product_id = products.id

     WHERE wishlist.user_id = ?

     ORDER BY wishlist.created_at DESC"

);


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$products =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        My Wishlist - Sulax Shoes Collection
    </title>

    <link rel="stylesheet"
          href="style.css">


    <style>

        .wishlist-container {

            max-width: 1200px;

            margin: 40px auto;

            padding: 20px;

        }


        .wishlist-container h1 {

            margin-bottom: 25px;

        }


        .wishlist-grid {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

        }


        .wishlist-card {

            background: white;

            border-radius: 8px;

            overflow: hidden;

            box-shadow:
                0 2px 10px
                rgba(0,0,0,0.08);

        }


        .wishlist-card img {

            width: 100%;

            height: 220px;

            object-fit: cover;

        }


        .wishlist-info {

            padding: 15px;

        }


        .wishlist-info h3 {

            margin: 5px 0 10px;

        }


        .category {

            color: #777;

            font-size: 13px;

        }


        .rating {

            margin: 8px 0;

        }


        .price {

            color: #f85606;

            font-size: 20px;

            font-weight: bold;

        }


        .old-price {

            color: #999;

            text-decoration: line-through;

            margin-left: 5px;

            font-size: 13px;

        }


        .buttons {

            display: flex;

            gap: 8px;

            margin-top: 15px;

        }


        .buttons a {

            flex: 1;

            text-align: center;

            padding: 9px;

            border-radius: 5px;

            text-decoration: none;

            font-size: 14px;

        }


        .view-btn {

            background: #f85606;

            color: white;

        }


        .remove-btn {

            background: #eee;

            color: #333;

        }


        .remove-btn:hover {

            background: #ddd;

        }


        .empty-wishlist {

            background: white;

            padding: 60px 20px;

            text-align: center;

            border-radius: 8px;

        }


        .empty-wishlist .heart {

            font-size: 60px;

            margin-bottom: 15px;

        }


        .shop-btn {

            display: inline-block;

            margin-top: 15px;

            padding: 12px 25px;

            background: #f85606;

            color: white;

            text-decoration: none;

            border-radius: 5px;

        }


        @media (max-width: 1000px) {

            .wishlist-grid {

                grid-template-columns:
                    repeat(3, 1fr);

            }

        }


        @media (max-width: 700px) {

            .wishlist-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 450px) {

            .wishlist-grid {

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


        <div class="nav-links">


            <a href="index.php">

                Home

            </a>


            <a href="account.php">

                👤 My Account

            </a>


            <a href="wishlist.php">

                ❤️ Wishlist

            </a>


            <a href="cart.php">

                🛒 Cart

            </a>


            <a href="logout.php">

                Logout

            </a>


        </div>


    </div>

</header>



<!-- ==========================================
     WISHLIST
========================================== -->

<div class="wishlist-container">


    <h1>

        ❤️ My Wishlist

    </h1>


    <?php if (
        $products->num_rows > 0
    ): ?>


        <div class="wishlist-grid">


            <?php while (
                $product =
                $products->fetch_assoc()
            ): ?>


                <div class="wishlist-card">


                    <!-- PRODUCT IMAGE -->

                    <?php if (
                        !empty(
                            $product["image"]
                        )
                    ): ?>

                        <img
                            src="uploads/<?php
                            echo htmlspecialchars(
                                $product["image"]
                            );
                            ?>"
                            alt="<?php
                            echo htmlspecialchars(
                                $product["name"]
                            );
                            ?>"
                        >

                    <?php else: ?>

                        <div
                            style="
                                height:220px;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#eee;
                            "
                        >

                            No Image

                        </div>

                    <?php endif; ?>


                    <div class="wishlist-info">


                        <div class="category">

                            <?php
                            echo htmlspecialchars(
                                $product["category"]
                            );
                            ?>

                        </div>


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $product["name"]
                            );
                            ?>

                        </h3>


                        <div class="rating">

                            ⭐
                            <?php
                            echo number_format(
                                $product["rating"],
                                1
                            );
                            ?>

                        </div>


                        <div>

                            <span class="price">

                                Rs.
                                <?php
                                echo number_format(
                                    $product["price"],
                                    2
                                );
                                ?>

                            </span>


                            <?php if (
                                $product["old_price"]
                            ): ?>

                                <span class="old-price">

                                    Rs.
                                    <?php
                                    echo number_format(
                                        $product["old_price"],
                                        2
                                    );
                                    ?>

                                </span>

                            <?php endif; ?>

                        </div>


                        <?php if (
                            $product["stock"] > 0
                        ): ?>

                            <p style="color:green;">

                                ✓ In Stock

                            </p>

                        <?php else: ?>

                            <p style="color:red;">

                                ✕ Out of Stock

                            </p>

                        <?php endif; ?>


                        <div class="buttons">


                            <a
                                href="product-details.php?id=<?php
                                echo $product["id"];
                                ?>"
                                class="view-btn"
                            >

                                View Product

                            </a>


                            <a
                                href="wishlist.php?remove=<?php
                                echo $product["id"];
                                ?>"
                                class="remove-btn"
                                onclick="
                                    return confirm(
                                        'Remove this product from wishlist?'
                                    );
                                "
                            >

                                Remove

                            </a>


                        </div>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty-wishlist">


            <div class="heart">

                ❤️

            </div>


            <h2>

                Your Wishlist is Empty

            </h2>


            <p>

                Save your favorite shoes here
                and buy them later.

            </p>


            <a
                href="index.php"
                class="shop-btn"
            >

                Start Shopping

            </a>


        </div>


    <?php endif; ?>


</div>


</body>

</html>