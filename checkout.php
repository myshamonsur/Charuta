<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

// User must be logged in

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}

$userId = (int) $_SESSION["user_id"];

// Get cart items

$stmt = $pdo->prepare("
    SELECT
        ci.product_id,
        ci.quantity,
        p.name,
        p.price,
        p.image,
        p.stock
    FROM cart_items ci
    INNER JOIN products p
        ON ci.product_id = p.id
    WHERE ci.user_id = ?
    ORDER BY ci.id DESC
");

$stmt->execute([$userId]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If cart is empty

if (!$cartItems) {

    header("Location: cart.php");
    exit;

}

// Calculate total

$totalAmount = 0;

foreach ($cartItems as $item) {

    $totalAmount +=
        (float) $item["price"] *
        (int) $item["quantity"];

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Checkout - Charuta</title>

<link
    rel="stylesheet"
    href="assets/css/style.css"
>

<style>

    .checkout-section {
        padding: 50px 20px;
    }

    .checkout-container {
        max-width: 1100px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 30px;
    }

    .checkout-card {
        background: #ffffff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .checkout-card h2 {
        margin-top: 0;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 7px;
        font-size: 15px;
        box-sizing: border-box;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .payment-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 7px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .payment-option input {
        width: auto;
    }

    .order-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }

    .order-item-image {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #eee;
    }

    .order-item-placeholder {
        width: 70px;
        height: 70px;
        border-radius: 8px;
        background: #f3f3f3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #777;
    }

    .order-item-info {
        flex: 1;
    }

    .order-item-name {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .order-item-price {
        color: #666;
        font-size: 14px;
    }

    .order-total {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 2px solid #eee;
        font-size: 20px;
        font-weight: 700;
    }

    .place-order-btn {
        width: 100%;
        margin-top: 25px;
        padding: 14px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
    }

    .back-link {
        display: inline-block;
        margin-top: 15px;
        text-decoration: none;
    }

    @media (max-width: 768px) {

        .checkout-container {
            grid-template-columns: 1fr;
        }

    }

</style>
```

</head>

<body>

<header>

```
<div class="container">

    <div class="logo">

        <a href="index.php">

            <img
                src="assets/images/Charuta_Logo.png"
                alt="Charuta"
                class="site-logo"
            >

        </a>

    </div>

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
            Orders
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</div>
```

</header>

<section class="checkout-section">

```
<div class="checkout-container">

    <!-- CUSTOMER INFORMATION -->

    <div class="checkout-card">

        <h2>
            Delivery Information
        </h2>

        <form
            action="place_order.php"
            method="POST"
        >

            <div class="form-group">

                <label for="delivery_name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="delivery_name"
                    name="delivery_name"
                    placeholder="Enter your full name"
                    required
                >

            </div>

            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >

            </div>

            <div class="form-group">

                <label for="address">
                    Delivery Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    placeholder="Enter your complete delivery address"
                    required
                ></textarea>

            </div>

            <div class="form-group">

                <label>
                    Payment Method
                </label>

                <label class="payment-option">

                    <input
                        type="radio"
                        name="payment_method"
                        value="Cash on Delivery"
                        checked
                    >

                    <span>
                        Cash on Delivery
                    </span>

                </label>

                <label class="payment-option">

                    <input
                        type="radio"
                        name="payment_method"
                        value="Online Payment"
                    >

                    <span>
                        Online Payment
                    </span>

                </label>

            </div>

            <button
                type="submit"
                class="btn place-order-btn"
            >
                Place Order
            </button>

            <a
                href="cart.php"
                class="back-link"
            >
                ← Back to Cart
            </a>

        </form>

    </div>

    <!-- ORDER SUMMARY -->

    <div class="checkout-card">

        <h2>
            Order Summary
        </h2>

        <?php foreach ($cartItems as $item): ?>

            <?php

            $imageName = basename(
                trim(
                    (string) ($item["image"] ?? "")
                )
            );

            $imagePath =
                __DIR__ .
                "/assets/images/" .
                $imageName;

            $imageUrl =
                "assets/images/" .
                rawurlencode($imageName);

            $itemSubtotal =
                (float) $item["price"] *
                (int) $item["quantity"];

            ?>

            <div class="order-item">

                <?php if (
                    $imageName !== "" &&
                    is_file($imagePath)
                ): ?>

                    <img
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
                        class="order-item-image"
                    >

                <?php else: ?>

                    <div class="order-item-placeholder">
                        No Image
                    </div>

                <?php endif; ?>

                <div class="order-item-info">

                    <div class="order-item-name">

                        <?php
                        echo htmlspecialchars(
                            $item["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </div>

                    <div class="order-item-price">

                        <?php
                        echo number_format(
                            (float) $item["price"],
                            2
                        );
                        ?>

                        ×

                        <?php
                        echo (int) $item["quantity"];
                        ?>

                    </div>

                </div>

                <div>

                    <?php
                    echo number_format(
                        $itemSubtotal,
                        2
                    );
                    ?>

                </div>

            </div>

        <?php endforeach; ?>

        <div class="order-total">

            <span>
                Total
            </span>

            <span>

                <?php
                echo number_format(
                    $totalAmount,
                    2
                );
                ?>

            </span>

        </div>

    </div>

</div>
```

</section>

</body>

</html>
