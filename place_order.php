<?php

session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: checkout.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$deliveryName = trim($_POST["delivery_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$address = trim($_POST["address"] ?? "");
$paymentMethod = trim($_POST["payment_method"] ?? "Cash on Delivery");

if ($deliveryName === "" || $phone === "" || $address === "") {
    die("Please fill in all required fields.");
}

try {

    $pdo->beginTransaction();

    // Get cart items with current product information
    $stmt = $pdo->prepare("
        SELECT 
            c.product_id,
            c.quantity,
            p.name,
            p.price,
            p.stock
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        FOR UPDATE
    ");

    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) {
        throw new Exception("Your cart is empty.");
    }

    $totalAmount = 0;

    // Check stock and calculate total
    foreach ($cartItems as $item) {

        $quantity = (int) $item["quantity"];
        $stock = (int) $item["stock"];
        $price = (float) $item["price"];

        if ($quantity <= 0) {
            throw new Exception("Invalid product quantity.");
        }

        if ($quantity > $stock) {
            throw new Exception(
                "Not enough stock for " . $item["name"] .
                ". Available stock: " . $stock
            );
        }

        $totalAmount += $price * $quantity;
    }

    // Insert order
    $orderStmt = $pdo->prepare("
        INSERT INTO orders
        (
            user_id,
            total_amount,
            delivery_name,
            phone,
            address,
            payment_method,
            payment_status,
            order_status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $paymentStatus = "Pending";
    $orderStatus = "Pending";

    $orderStmt->execute([
        $userId,
        $totalAmount,
        $deliveryName,
        $phone,
        $address,
        $paymentMethod,
        $paymentStatus,
        $orderStatus
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items
        (
            order_id,
            product_id,
            quantity,
            unit_price
        )
        VALUES (?, ?, ?, ?)
    ");

    // Reduce product stock
    $stockStmt = $pdo->prepare("
        UPDATE products
        SET stock = stock - ?
        WHERE id = ?
    ");

    foreach ($cartItems as $item) {

        $itemStmt->execute([
            $orderId,
            $item["product_id"],
            $item["quantity"],
            $item["price"]
        ]);

        $stockStmt->execute([
            $item["quantity"],
            $item["product_id"]
        ]);
    }

    // Clear user's cart
    $clearCartStmt = $pdo->prepare("
        DELETE FROM cart
        WHERE user_id = ?
    ");

    $clearCartStmt->execute([$userId]);

    $pdo->commit();

    // Go to orders page
    header("Location: orders.php?success=1");
    exit;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo "<h2>Order could not be placed.</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>