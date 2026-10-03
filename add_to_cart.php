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
$productId = filter_input(
    INPUT_POST,
    "product_id",
    FILTER_VALIDATE_INT
);

$quantity = filter_input(
    INPUT_POST,
    "quantity",
    FILTER_VALIDATE_INT
);

// Validate product ID and quantity
if (
    $productId === false ||
    $productId === null ||
    $productId < 1 ||
    $quantity === false ||
    $quantity === null ||
    $quantity < 1
) {
    header("Location: products.php");
    exit;
}

// Get product information
$stmt = $pdo->prepare(
    "SELECT id, price, stock
     FROM products
     WHERE id = ?"
);

$stmt->execute([$productId]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

// Check stock
$stock = (int)$product["stock"];

if ($stock < 1) {
    header("Location: product_details.php?id=" . $productId);
    exit;
}

// Quantity cannot be greater than stock
if ($quantity > $stock) {
    $quantity = $stock;
}

// Find user's cart
$stmt = $pdo->prepare(
    "SELECT id
     FROM cart
     WHERE user_id = ?"
);

$stmt->execute([$userId]);

$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cart) {

    // Create new cart
    $stmt = $pdo->prepare(
        "INSERT INTO cart (user_id)
         VALUES (?)"
    );

    $stmt->execute([$userId]);

    $cartId = (int)$pdo->lastInsertId();

} else {

    $cartId = (int)$cart["id"];
}

// Check if product already exists in cart
$stmt = $pdo->prepare(
    "SELECT id, quantity
     FROM cart_items
     WHERE cart_id = ?
     AND product_id = ?"
);

$stmt->execute([
    $cartId,
    $productId
]);

$cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cartItem) {

    $newQuantity =
        (int)$cartItem["quantity"] + $quantity;

    // Do not exceed available stock
    if ($newQuantity > $stock) {
        $newQuantity = $stock;
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

// Go to cart
header("Location: cart.php");
exit;