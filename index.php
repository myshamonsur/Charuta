<?php
session_start();
require_once "includes/db.php";

// Get latest 4 products
$stmt = $pdo->query(
    "SELECT id, name, category, description, price, image
     FROM products
     ORDER BY id DESC
     LIMIT 4"
);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Charuta | Beauty & Skincare</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* Charuta logo */
        header .brand-logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 190px;
            height: 65px;
            flex-shrink: 0;
            text-decoration: none;
            overflow: hidden;
        }

        header .brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Product image */
        .product-image {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: #fff;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        /* If image is missing */
        .product-placeholder {
            width: 100%;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5e9ed;
            border-radius: 10px;
            margin-bottom: 12px;
            color: #76515e;
        }

        .product-card {
            overflow: hidden;
        }

        @media (max-width: 600px) {
            header .brand-logo {
                width: 130px;
                height: 55px;
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

    <!-- Hero Section -->
    <section class="hero">

        <div class="hero-content">

            <span class="eyebrow">
                YOUR DAILY BEAUTY DESTINATION
            </span>

            <h1>
                Glow Naturally with Charuta
            </h1>

            <p>
                Discover skincare products that make your
                everyday beauty routine simple and special.
                Find the right products for your daily needs.
            </p>

            <a href="products.php" class="btn">
                Shop Now
            </a>

        </div>

    </section>


    <!-- Featured Products -->
    <section class="section">

        <h2>
            Featured Products
        </h2>

        <p class="section-description">
            Explore some of our latest skincare products.
        </p>


        <div class="product-grid">

            <?php if (!empty($products)): ?>

                <?php foreach ($products as $product): ?>

                    <?php
                    /*
                     * Get image filename from database.
                     * basename() prevents folder path problems.
                     */
                    $imageName = basename(
                        trim((string)($product["image"] ?? ""))
                    );

                    /*
                     * Physical file path.
                     * This checks whether the image really exists.
                     */
                    $imagePath = __DIR__ .
                        "/assets/images/" .
                        $imageName;

                    /*
                     * Browser URL.
                     * rawurlencode() handles spaces and special
                     * characters in filenames.
                     */
                    $imageUrl = "assets/images/" .
                        rawurlencode($imageName);
                    ?>


                    <div class="product-card">

                        <!-- Product Image -->
                        <?php if (
                            $imageName !== "" &&
                            is_file($imagePath)
                        ): ?>

                            <img
                                class="product-image"
                                src="<?php
                                    echo htmlspecialchars(
                                        $imageUrl,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>"
                                alt="<?php
                                    echo htmlspecialchars(
                                        $product["name"],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div class="product-placeholder">
                                <?php
                                echo htmlspecialchars(
                                    $product["category"] ?? "Product",
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </div>

                        <?php endif; ?>


                        <!-- Product Name -->
                        <h3>
                            <?php
                            echo htmlspecialchars(
                                $product["name"],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </h3>


                        <!-- Category -->
                        <p>
                            <?php
                            echo htmlspecialchars(
                                $product["category"] ?? "",
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </p>


                        <!-- Description -->
                        <p>

                            <?php

                            $description =
                                $product["description"] ?? "";

                            echo htmlspecialchars(
                                strlen($description) > 100
                                    ? substr(
                                        $description,
                                        0,
                                        100
                                    ) . "..."
                                    : $description,
                                ENT_QUOTES,
                                'UTF-8'
                            );

                            ?>

                        </p>


                        <!-- Price -->
                        <div class="price">

                            ৳<?php
                            echo number_format(
                                (float)$product["price"],
                                2
                            );
                            ?>

                        </div>


                        <!-- View Details -->
                        <a
                            class="btn"
                            href="product_details.php?id=<?php
                                echo (int)$product["id"];
                            ?>"
                        >
                            View Details
                        </a>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="empty-message">
                    No products available yet.
                    Please visit us again soon.
                </p>

            <?php endif; ?>

        </div>


        <!-- View All Products -->
        <a
            href="products.php"
            class="btn secondary-btn"
        >
            View All Products
        </a>

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