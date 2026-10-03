<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "all");


// Fetch categories

$categoryStmt = $pdo->query(
    "SELECT TRIM(category) AS category
     FROM products
     WHERE category IS NOT NULL
     AND TRIM(category) <> ''
     GROUP BY TRIM(category)
     ORDER BY TRIM(category) ASC"
);

$categories = $categoryStmt->fetchAll(
    PDO::FETCH_COLUMN
);


// Build product query

$sql = "
    SELECT
        id,
        name,
        category,
        description,
        price,
        stock,
        image
    FROM products
    WHERE 1=1
";

$params = [];


// Search

if ($search !== "") {

    $sql .= "
        AND (
            name LIKE ?
            OR description LIKE ?
        )
    ";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}


// Category filter

if (
    $category !== "" &&
    strtolower($category) !== "all"
) {

    $sql .= "
        AND LOWER(TRIM(category))
        =
        LOWER(TRIM(?))
    ";

    $params[] = $category;
}


// Sort

$sql .= " ORDER BY id DESC";


// Execute query

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$products = $stmt->fetchAll(
    PDO::FETCH_ASSOC
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Products | Charuta</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        header .brand-logo {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 190px;
            height: 65px;
            flex-shrink: 0;
            overflow: hidden;
            text-decoration: none;
        }

        header .brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }


        .search-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 25px 0;
        }

        .search-form input,
        .search-form select {
            min-width: 180px;
            flex: 1;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            background: #fff;
        }

        .search-form button,
        .search-form a {
            white-space: nowrap;
        }


        .product-image {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: contain;
            background: #fff;
            border-radius: 10px;
            margin-bottom: 12px;
        }


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

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form input,
            .search-form select {
                width: 100%;
                box-sizing: border-box;
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

</header>


<main class="section">


    <h2>
        Our Products
    </h2>


    <p class="section-description">
        Find the right skincare products for your daily routine.
    </p>


    <form
        method="GET"
        action="products.php"
        class="search-form"
    >

        <input
            type="search"
            name="search"
            placeholder="Search products..."
            value="<?php
                echo htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    "UTF-8"
                );
            ?>"
        >


        <select
            name="category"
            onchange="this.form.submit()"
        >

            <option
                value="all"
                <?php
                if (
                    $category === "" ||
                    strtolower($category) === "all"
                ) {
                    echo "selected";
                }
                ?>
            >
                All Categories
            </option>


            <?php foreach ($categories as $cat): ?>

                <option
                    value="<?php
                        echo htmlspecialchars(
                            $cat,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    <?php
                    if (
                        strcasecmp(
                            trim($category),
                            trim($cat)
                        ) === 0
                    ) {
                        echo "selected";
                    }
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $cat,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </option>

            <?php endforeach; ?>

        </select>


        <button
            type="submit"
            class="btn"
        >
            Search
        </button>


        <a
            href="products.php"
            class="btn secondary-btn"
        >
            Clear
        </a>


    </form>


    <div class="product-grid">


        <?php if (count($products) > 0): ?>


            <?php foreach ($products as $product): ?>


                <?php

                $imageName = basename(
                    trim(
                        (string)($product["image"] ?? "")
                    )
                );

                $imagePath =
                    __DIR__ .
                    "/assets/images/" .
                    $imageName;

                $imageUrl =
                    "assets/images/" .
                    rawurlencode($imageName);

                ?>


                <div class="product-card">


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
                            loading="lazy"
                        >

                    <?php else: ?>

                        <div class="product-placeholder">

                            <?php
                            echo htmlspecialchars(
                                $product["category"] ?? "Product",
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </div>

                    <?php endif; ?>


                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $product["name"],
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </h3>


                    <p>

                        Category:

                        <?php
                        echo htmlspecialchars(
                            $product["category"] ?? "",
                            ENT_QUOTES,
                            "UTF-8"
                        );
                        ?>

                    </p>


                    <p>

                        <?php

                        $description =
                            $product["description"] ?? "";

                        if (strlen($description) > 120) {

                            $description =
                                substr(
                                    $description,
                                    0,
                                    120
                                ) . "...";

                        }

                        echo htmlspecialchars(
                            $description,
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </p>


                    <div class="price">

                        ৳<?php

                        echo number_format(
                            (float)$product["price"],
                            2
                        );

                        ?>

                    </div>


                    <p class="stock">

                        <?php if (
                            (int)$product["stock"] > 0
                        ): ?>

                            In Stock:
                            <?php echo (int)$product["stock"]; ?>

                        <?php else: ?>

                            Out of Stock

                        <?php endif; ?>

                    </p>


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
                No products found.
                Try another search or category.
            </p>

        <?php endif; ?>


    </div>


</main>


<footer>

    <p>

        &copy; <?php echo date("Y"); ?>

        Charuta. All rights reserved.

    </p>

</footer>


</body>

</html>