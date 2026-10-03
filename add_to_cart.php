<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: products.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$productId = isset($_POST["product_id"]) ? (int) $_POST["product_id"] : 0;
$quantity = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 1;

if ($productId <= 0) {
    header("Location: products.php");
    exit;
}

if ($quantity <= 0) {
    $quantity = 1;
}

try {

    // Check product
    $stmt = $pdo->prepare(
        "SELECT id, name, price, stock
         FROM products
         WHERE id = ?"
    );

    $stmt->execute([$productId]);

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Check stock
    if ((int) $product["stock"] <= 0) {
        die("This product is out of stock.");
    }

    // Do not allow quantity above stock
    if ($quantity > (int) $product["stock"]) {
        $quantity = (int) $product["stock"];
    }

    // Check if this product is already in user's cart
    $stmt = $pdo->prepare(
        "SELECT id, quantity
         FROM cart_items
         WHERE user_id = ?
         AND product_id = ?
         LIMIT 1"
    );

    $stmt->execute([
        $userId,
        $productId
    ]);

    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {

        // Product already exists
        $newQuantity = (int) $cartItem["quantity"] + $quantity;

        if ($newQuantity > (int) $product["stock"]) {
            $newQuantity = (int) $product["stock"];
        }

        $stmt = $pdo->prepare(
            "UPDATE cart_items
             SET quantity = ?
             WHERE id = ?
             AND user_id = ?"
        );

        $stmt->execute([
            $newQuantity,
            (int) $cartItem["id"],
            $userId
        ]);

    } else {

        // Add new product
        $stmt = $pdo->prepare(
            "INSERT INTO cart_items
             (user_id, product_id, quantity)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $userId,
            $productId,
            $quantity
        ]);
    }

    header("Location: cart.php");
    exit;

} catch (PDOException $e) {

    die("Add to cart failed: " . $e->getMessage());
}