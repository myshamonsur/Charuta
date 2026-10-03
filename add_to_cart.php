```php
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

$userId = (int)$_SESSION["user_id"];

$productId = isset($_POST["product_id"]) ? (int)$_POST["product_id"] : 0;
$quantity = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 1;

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

    if ((int)$product["stock"] <= 0) {
        die("This product is out of stock.");
    }

    // Make sure quantity does not exceed stock
    if ($quantity > (int)$product["stock"]) {
        $quantity = (int)$product["stock"];
    }

    // Find user's cart
    $stmt = $pdo->prepare(
        "SELECT id
         FROM cart
         WHERE user_id = ?
         LIMIT 1"
    );

    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    // Create cart if it does not exist
    if (!$cart) {

        $stmt = $pdo->prepare(
            "INSERT INTO cart (user_id)
             VALUES (?)"
        );

        $stmt->execute([$userId]);

        $cartId = (int)$pdo->lastInsertId();

    } else {

        $cartId = (int)$cart["id"];
    }

    // Check if product is already in cart
    $stmt = $pdo->prepare(
        "SELECT id, quantity
         FROM cart_items
         WHERE cart_id = ?
         AND product_id = ?
         LIMIT 1"
    );

    $stmt->execute([
        $cartId,
        $productId
    ]);

    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {

        $newQuantity = (int)$cartItem["quantity"] + $quantity;

        if ($newQuantity > (int)$product["stock"]) {
            $newQuantity = (int)$product["stock"];
        }

        $stmt = $pdo->prepare(
            "UPDATE cart_items
             SET quantity = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $newQuantity,
            (int)$cartItem["id"]
        ]);

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO cart_items
             (cart_id, product_id, quantity)
             VALUES (?, ?, ?)"
        );

        $stmt->execute([
            $cartId,
            $productId,
            $quantity
        ]);
    }

    header("Location: cart.php");
    exit;

} catch (PDOException $e) {

    die("Add to cart failed: " . $e->getMessage());
}
```
