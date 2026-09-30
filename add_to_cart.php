<?php
session_start();
require_once "includes/db.php";

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Only POST request allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: products.php");
    exit;
}

// Validate product ID
$productId = $_POST["product_id"] ?? "";
$quantity = $_POST["quantity"] ?? "";

if (
    !ctype_digit((string)$productId) ||
    (int)$productId < 1 ||
    !ctype_digit((string)$quantity) ||
    (int)$quantity < 1
) {
    header("Location: products.php");
    exit;
}

$productId = (int)$productId;
$quantity = (int)$quantity;
$userId = (int)$_SESSION["user_id"];

// Check product
$stmt = $pdo->prepare(
    "SELECT id, stock
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

// Check existing cart item
$stmt = $pdo->prepare(
    "SELECT id, quantity
     FROM cart
     WHERE user_id = ?
       AND product_id = ?"
);

$stmt->execute([
    $userId,
    $productId
]);

$existingCart = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingCart) {

    // Add new quantity to existing quantity
    $newQuantity =
        (int)$existingCart["quantity"] + $quantity;

    // Do not allow quantity above stock
    if ($newQuantity > $stock) {
        $newQuantity = $stock;
    }

    $stmt = $pdo->prepare(
        "UPDATE cart
         SET quantity = ?
         WHERE id = ?"
    );

    $stmt->execute([
        $newQuantity,
        (int)$existingCart["id"]
    ]);

} else {

    // Add new product to cart
    if ($quantity > $stock) {
        $quantity = $stock;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO cart
            (user_id, product_id, quantity)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $productId,
        $quantity
    ]);
}

// Go to cart
header("Location: cart.php");
exit;
?>