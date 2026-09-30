<?php
session_start();
require_once "includes/db.php";

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

// Fetch cart items
$stmt = $pdo->prepare(
    "SELECT
        cart.id AS cart_id,
        cart.quantity,
        products.id AS product_id,
        products.name,
        products.price,
        products.image,
        products.stock
     FROM cart
     INNER JOIN products
        ON cart.product_id = products.id
     WHERE cart.user_id = ?
     ORDER BY cart.id DESC"
);

$stmt->execute([$userId]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;

foreach ($cartItems as $item) {
    $total +=
        (float)$item["price"] *
        (int)$item["quantity"];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Cart | Charuta</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .cart-section {
            max-width: 1100px;
            margin: 50px auto;
            padding: 20px;
        }

        .cart-title {
            color: #713d56;
            margin-bottom: 25px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 20px;
            align-items: center;

            background: #fff;
            padding: 20px;
            margin-bottom: 15px;

            border-radius: 12px;

            box-shadow:
                0 4px 18px
                rgba(0, 0, 0, 0.08);
        }

        .cart-image {
            width: 120px;
            height: 120px;
            object-fit: contain;

            background: #f9eaf0;
            border-radius: 10px;
        }

        .cart-info h3 {
            margin: 0 0 8px;
            color: #713d56;
        }

        .cart-price {
            color: #a64d78;
            font-weight: bold;
        }

        .cart-quantity {
            margin-top: 10px;
        }

        .cart-quantity input {
            width: 70px;
            padding: 8px;

            border: 1px solid #d9b6c6;
            border-radius: 6px;
        }

        .cart-subtotal {
            text-align: right;
            font-weight: bold;
            color: #713d56;
        }

        .cart-actions {
            margin-top: 30px;
            padding: 25px;

            background: #fff;
            border-radius: 12px;

            box-shadow:
                0 4px 18px
                rgba(0, 0, 0, 0.08);
        }

        .cart-total {
            font-size: 24px;
            font-weight: bold;
            color: #a64d78;
            margin-bottom: 20px;
        }

        .empty-cart {
            background: #fff;
            padding: 40px;
            text-align: center;
            border-radius: 12px;
        }

        @media (max-width: 700px) {

            .cart-item {
                grid-template-columns: 90px 1fr;
            }

            .cart-image {
                width: 90px;
                height: 90px;
            }

            .cart-subtotal {
                grid-column: 2;
                text-align: left;
            }

        }

    </style>

</head>

<body>

<header>

    <a
        href="index.php"
        class="brand-logo"
    >
        <img
            src="assets/images/Charuta_Logo.png"
            alt="Charuta Beauty and Skincare"
        >
    </a>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="cart.php">
            Cart
        </a>

        <a href="orders.php">
            My Orders
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<main>

<section class="cart-section">

    <h2 class="cart-title">
        My Shopping Cart
    </h2>


    <?php if (!empty($cartItems)): ?>

        <?php foreach ($cartItems as $item): ?>

            <?php

            $imageName = basename(
                trim(
                    (string)($item["image"] ?? "")
                )
            );

            $imagePath =
                __DIR__ .
                "/assets/images/" .
                $imageName;

            $imageUrl =
                "assets/images/" .
                rawurlencode($imageName);

            $subtotal =
                (float)$item["price"] *
                (int)$item["quantity"];

            ?>

            <div class="cart-item">

                <?php if (
                    $imageName !== "" &&
                    is_file($imagePath)
                ): ?>

                    <img
                        class="cart-image"
                        src="<?php
                            echo htmlspecialchars(
                                $imageUrl,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                        alt="<?php
                            echo htmlspecialchars(
                                $item["name"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                    >

                <?php else: ?>

                    <div class="cart-image"></div>

                <?php endif; ?>


                <div class="cart-info">

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $item["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>
                    </h3>

                    <p class="cart-price">
                        ৳<?php
                        echo number_format(
                            (float)$item["price"],
                            2
                        );
                        ?>
                    </p>

                    <p class="cart-quantity">

                        Quantity:
                        <?php
                        echo (int)$item["quantity"];
                        ?>

                    </p>

                    <a
                        href="remove_from_cart.php?id=<?php
                            echo (int)$item["cart_id"];
                        ?>"
                        class="btn secondary-btn"
                    >
                        Remove
                    </a>

                </div>


                <div class="cart-subtotal">

                    Subtotal

                    <br>

                    ৳<?php
                    echo number_format(
                        $subtotal,
                        2
                    );
                    ?>

                </div>

            </div>

        <?php endforeach; ?>


        <div class="cart-actions">

            <div class="cart-total">

                Total:
                ৳<?php
                echo number_format(
                    $total,
                    2
                );
                ?>

            </div>


            <a
                href="checkout.php"
                class="btn"
            >
                Proceed to Checkout
            </a>

            <a
                href="products.php"
                class="btn secondary-btn"
            >
                Continue Shopping
            </a>

        </div>


    <?php else: ?>

        <div class="empty-cart">

            <h2>
                Your Cart is Empty
            </h2>

            <p>
                Add some skincare products
                to your cart first.
            </p>

            <a
                href="products.php"
                class="btn"
            >
                Browse Products
            </a>

        </div>

    <?php endif; ?>

</section>

</main>


<footer>

    <p>
        &copy; <?php echo date("Y"); ?>
        Charuta. All rights reserved.
    </p>

</footer>

</body>

</html>