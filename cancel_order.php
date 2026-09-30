<?php

session_start();
require_once "includes/db.php";

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// Only POST request is allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: orders.php");
    exit;
}


$userId = (int) $_SESSION["user_id"];

$orderId = (int) (
    $_POST["order_id"] ?? 0
);


// Validate order ID
if ($orderId <= 0) {

    header("Location: orders.php");
    exit;

}


try {

    // Start transaction
    $pdo->beginTransaction();


    // ==================================================
    // 1. Find the user's order
    // ==================================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            order_status
        FROM orders
        WHERE id = ?
        AND user_id = ?
        FOR UPDATE
    ");

    $stmt->execute([
        $orderId,
        $userId
    ]);

    $order = $stmt->fetch(
        PDO::FETCH_ASSOC
    );


    // Order not found
    if (!$order) {

        throw new Exception(
            "Order not found."
        );

    }


    // ==================================================
    // 2. Only Pending orders can be cancelled
    // ==================================================

    if (
        strcasecmp(
            trim($order["order_status"]),
            "Pending"
        ) !== 0
    ) {

        throw new Exception(
            "This order cannot be cancelled."
        );

    }


    // ==================================================
    // 3. Get all products from this order
    // ==================================================

    $stmt = $pdo->prepare("
        SELECT
            product_id,
            quantity
        FROM order_items
        WHERE order_id = ?
    ");

    $stmt->execute([
        $orderId
    ]);

    $items = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


    // No products found
    if (!$items) {

        throw new Exception(
            "No products found for this order."
        );

    }


    // ==================================================
    // 4. Restore product stock
    // ==================================================

    $stmt = $pdo->prepare("
        UPDATE products
        SET stock = stock + ?
        WHERE id = ?
    ");


    foreach ($items as $item) {

        $quantity = (int) $item["quantity"];

        $productId = (int) $item["product_id"];


        $stmt->execute([
            $quantity,
            $productId
        ]);


        // Product does not exist
        if ($stmt->rowCount() !== 1) {

            throw new Exception(
                "Product stock could not be restored."
            );

        }

    }


    // ==================================================
    // 5. Change order status to Cancelled
    // ==================================================

    $stmt = $pdo->prepare("
        UPDATE orders
        SET order_status = 'Cancelled'
        WHERE id = ?
        AND user_id = ?
        AND order_status = 'Pending'
    ");

    $stmt->execute([
        $orderId,
        $userId
    ]);


    // Order was not updated
    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            "Order could not be cancelled."
        );

    }


    // ==================================================
    // 6. Commit everything
    // ==================================================

    $pdo->commit();


    // Return to orders page
    header(
        "Location: orders.php?cancelled=1"
    );

    exit;


} catch (Exception $e) {


    // Rollback if transaction is active
    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    // Show error on orders page
    header(
        "Location: orders.php?error=" .
        urlencode(
            $e->getMessage()
        )
    );

    exit;

}