<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: cart.php");
    exit;
}

$cartItemId = (int) $_GET["id"];

$stmt = $pdo->prepare(
    "DELETE FROM cart_items
     WHERE id = ?
     AND user_id = ?"
);

$stmt->execute([
    $cartItemId,
    $userId
]);

header("Location: cart.php");
exit;
