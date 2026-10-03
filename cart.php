<?php

require_once "includes/session.php";
require_once "includes/db.php";


// --------------------------------------------------
// User must be logged in
// --------------------------------------------------

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


$userId = (int)$_SESSION["user_id"];


// --------------------------------------------------
// Get cart items
// --------------------------------------------------

$stmt = $pdo->prepare(
    "SELECT
        ci.id AS cart_item_id,
        ci.quantity,
        p.id AS product_id,
        p.name,
        p.price,
        p.image,
        p.stock
     FROM cart c
     INNER JOIN cart_items ci
        ON c.id = ci.cart_id
     INNER JOIN products p
        ON ci.product_id = p.id
     WHERE c.user_id = ?
     ORDER BY ci.id DESC"
);

$stmt->execute([$userId]);

$cartItems = $stmt->fetchAll();


// --------------------------------------------------
// Calculate total
// --------------------------------------------------

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Shopping Cart - Charuta</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>

        .cart-container {
            max-width: 1000px;
            margin: 40px auto;
        }

        .cart-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
        }

        .cart-item img {
            width: 110px;
            height: 110px;
            object-fit: contain;
            border-radius: 8px;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-info h3 {
            margin-bottom: 10px;
        }

        .cart-item-info p {
            margin: 5px 0;
        }

        .cart-summary {
            margin-top: 30px;
            padding: 25px;
            border-radius: 10px;
            background: #f7f7f7;
            text-align: right;
        }

        .cart-total {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
        }

        .empty-cart p {
            margin-bottom: 20px;
        }

        @media (max-width: 700px) {

            .cart-item {
                flex-direction: column;
                text-align: center;
            }

        }

    </style>

</head>


<body>


<header>

    <div class="container nav-container">

        <a href="index.php" class="logo">
            Charuta
        </a>


        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>


            <?php if (isset($_SESSION["user_id"])): ?>

                <a href="cart.php">
                    Cart
                </a>

                <a href="orders.php">
                    My Orders
                </a>

                <a href="logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php">
                    Login
                </a>

                <a href="register.php">
                    Register
                </a>

            <?php endif; ?>

        </nav>

    </div>

</header>


<main class="container">

    <div class="cart-container">

        <h1 class="cart-title">
            Shopping Cart
        </h1>


        <?php if (empty($cartItems)): ?>

            <div class="empty-cart">

                <h2>
                    Your cart is empty
                </h2>

                <p>
                    Add some products to your cart.
                </p>

                <a
                    href="products.php"
                    class="btn"
                >
                    Continue Shopping
                </a>

            </div>


        <?php else: ?>


            <?php foreach ($cartItems as $item): ?>

                <div class="cart-item">


                    <?php

                    $imagePath = "";

                    if (!empty($item["image"])) {

                        $imagePath =
                            "assets/images/" .
                            $item["image"];
                    }

                    ?>


                    <?php if (
                        !empty($imagePath) &&
                        file_exists($imagePath)
                    ): ?>

                        <img
                            src="<?php echo htmlspecialchars(
                                $imagePath,
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            alt="<?php echo htmlspecialchars(
                                $item["name"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                        >

                    <?php endif; ?>


                    <div class="cart-item-info">

                        <h3>

                            <?php echo htmlspecialchars(
                                $item["name"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>

                        </h3>


                        <p>
                            Price:
                            ৳<?php echo number_format(
                                (float)$item["price"],
                                2
                            ); ?>
                        </p>


                        <p>
                            Quantity:
                            <?php echo (int)$item["quantity"]; ?>
                        </p>


                        <p>

                            Subtotal:

                            ৳<?php echo number_format(
                                (float)$item["price"] *
                                (int)$item["quantity"],
                                2
                            ); ?>

                        </p>

                    </div>


                    <div>

                        <a
                            href="remove_from_cart.php?id=<?php echo (int)$item["cart_item_id"]; ?>"
                            class="btn secondary-btn"
                            onclick="return confirm('Remove this item from cart?');"
                        >
                            Remove
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>


            <div class="cart-summary">

                <div class="cart-total">

                    Total:

                    ৳<?php echo number_format(
                        $total,
                        2
                    ); ?>

                </div>


                <a
                    href="products.php"
                    class="btn secondary-btn"
                >
                    Continue Shopping
                </a>


                <a
                    href="checkout.php"
                    class="btn"
                >
                    Proceed to Checkout
                </a>

            </div>


        <?php endif; ?>

    </div>

</main>


<footer>

    <div class="container">

        <p>
            &copy; 2026 Charuta. All rights reserved.
        </p>

    </div>

</footer>


</body>

</html>