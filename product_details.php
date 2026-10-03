<?php

require_once "includes/session.php";
require_once "includes/db.php";


// Validate product ID
if (
    !isset($_GET["id"]) ||
    !ctype_digit($_GET["id"]) ||
    (int)$_GET["id"] < 1
) {
    header("Location: products.php");
    exit;
}

$productId = (int)$_GET["id"];

// Fetch product
$stmt = $pdo->prepare(
    "SELECT id, name, category, description, price, image, stock
     FROM products
     WHERE id = ?"
);

$stmt->execute([$productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Return 404 if product does not exist
if (!$product) {

    http_response_code(404);

} else {

    // Correct image path
    $imageName = basename(
        trim((string)($product["image"] ?? ""))
    );

    $imagePath = __DIR__ . "/assets/images/" . $imageName;

    $imageUrl = "assets/images/" . rawurlencode($imageName);
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

    <title>

        <?php

        echo $product
            ? htmlspecialchars(
                $product["name"],
                ENT_QUOTES,
                "UTF-8"
            ) . " | Charuta"
            : "Product Not Found | Charuta";

        ?>

    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .details-section {
            max-width: 1100px;
            margin: 50px auto;
            padding: 20px;
        }

        .details-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
            align-items: start;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .details-image {
            width: 100%;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9eaf0;
            border-radius: 10px;
            overflow: hidden;
        }

        .details-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .details-placeholder {
            color: #985b76;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            padding: 20px;
        }

        .details-info h1 {
            color: #713d56;
            margin: 10px 0;
        }

        .details-category {
            color: #9b6680;
            font-weight: 600;
        }

        .details-description {
            line-height: 1.8;
            margin: 20px 0;
            color: #555;
        }

        .details-price {
            font-size: 26px;
            font-weight: bold;
            color: #a64d78;
            margin: 15px 0;
        }

        .stock-info {
            margin-bottom: 20px;
        }

        .quantity-input {
            display: block;
            width: 100px;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #d9b6c6;
            border-radius: 6px;
            font-size: 16px;
        }

        .details-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .details-message {
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            text-align: center;
        }

        @media (max-width: 700px) {

            .details-card {
                grid-template-columns: 1fr;
                padding: 18px;
            }

            .details-image {
                height: 320px;
            }

            .details-section {
                margin: 20px auto;
                padding: 12px;
            }

        }

    </style>

</head>

<body>

<header>

    <a href="index.php" class="brand-logo">

        <img
            src="assets/images/Charuta_Logo.png"
            alt="Charuta Beauty and Skincare"
        >

    </a>

    <nav>

        <a href="index.php">Home</a>

        <a href="products.php">Products</a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="cart.php">Cart</a>

            <a href="orders.php">My Orders</a>

            <a href="logout.php">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>

            <a href="register.php">Register</a>

        <?php endif; ?>

    </nav>

</header>

<main>

    <section class="details-section">

        <?php if (!$product): ?>

            <div class="details-message">

                <h2>
                    Product Not Found
                </h2>

                <p>
                    This product may have been removed
                    or does not exist.
                </p>

                <a
                    href="products.php"
                    class="btn"
                >
                    Back to Products
                </a>

            </div>

        <?php else: ?>

            <div class="details-card">

                <!-- Product Image -->

                <div class="details-image">

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
                                    $product["name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                        >

                    <?php else: ?>

                        <div class="details-placeholder">

                            <?php

                            echo htmlspecialchars(
                                $product["category"] ?? "Product",
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </div>

                    <?php endif; ?>

                </div>

                <!-- Product Information -->

                <div class="details-info">

                    <p class="details-category">

                        <?php

                        echo htmlspecialchars(
                            $product["category"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </p>

                    <h1>

                        <?php

                        echo htmlspecialchars(
                            $product["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </h1>

                    <p class="details-description">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $product["description"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            )
                        );

                        ?>

                    </p>

                    <div class="details-price">

                        ৳<?php

                        echo number_format(
                            (float)$product["price"],
                            2
                        );

                        ?>

                    </div>

                    <!-- Stock Information -->

                    <p class="stock-info">

                        <?php if ((int)$product["stock"] > 0): ?>

                            <span style="color: green;">

                                In Stock
                                (<?php
                                    echo (int)$product["stock"];
                                ?> available)

                            </span>

                        <?php else: ?>

                            <span style="color: red;">
                                Out of Stock
                            </span>

                        <?php endif; ?>

                    </p>

                    <!-- Add to Cart -->

                    <?php if (
                        (int)$product["stock"] > 0 &&
                        isset($_SESSION["user_id"])
                    ): ?>

                        <form
                            action="add_to_cart.php"
                            method="POST"
                        >

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php
                                    echo (int)$product["id"];
                                ?>"
                            >

                            <label for="quantity">
                                Quantity:
                            </label>

                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="quantity-input"
                                value="1"
                                min="1"
                                max="<?php
                                    echo (int)$product["stock"];
                                ?>"
                                required
                            >

                            <div class="details-actions">

                                <button
                                    type="submit"
                                    class="btn"
                                >
                                    Add to Cart
                                </button>

                                <a
                                    href="products.php"
                                    class="btn secondary-btn"
                                >
                                    Continue Shopping
                                </a>

                            </div>

                        </form>

                    <?php elseif (
                        (int)$product["stock"] > 0
                    ): ?>

                        <div class="details-actions">

                            <a
                                href="login.php"
                                class="btn"
                            >
                                Login to Add to Cart
                            </a>

                            <a
                                href="products.php"
                                class="btn secondary-btn"
                            >
                                Continue Shopping
                            </a>

                        </div>

                    <?php else: ?>

                        <a
                            href="products.php"
                            class="btn secondary-btn"
                        >
                            Continue Shopping
                        </a>

                    <?php endif; ?>

                </div>

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