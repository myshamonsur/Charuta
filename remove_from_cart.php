<?php
session_start();
require_once "includes/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (
    !isset($_GET["id"]) ||
    !ctype_digit($_GET["id"])
) {
    header("Location: cart.php");
    exit;
}

$cartId = (int)$_GET["id"];
$userId = (int)$_SESSION["user_id"];

$stmt = $pdo->prepare(
    "DELETE FROM cart
     WHERE id = ?
       AND user_id = ?"
);

$stmt->execute([
    $cartId,
    $userId
]);

header("Location: cart.php");
exit;
?>