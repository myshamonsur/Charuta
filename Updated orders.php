<?php

session_start();
require_once "includes/db.php";

// User must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

// Get user's orders
$stmt = $conn->prepare("
    SELECT
        id,
        total_amount,
        delivery_name,
        phone,
        address,
        payment_method,
        payment_status,
        order_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders - Charuta</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            color: #333;
        }

        /* =========================
           HEADER
        ========================= */

        header {
            background: white;
            padding: 15px 40px;
            border-bottom: 1px solid #ddd;
        }

        .navbar {
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo a {
            display: block;
        }

        .site-logo {
            height: 50px;
            width: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #8b5e3c;
        }

        /* =========================
           MAIN
        ========================= */

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            margin-bottom: 25px;
            color: #333;
        }

        /* =========================
           MESSAGES
        ========================= */

        .message {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 15px;
        }

        .success {
            background: #e8f7e8;
            color: #216b21;
            border: 1px solid #b9dfb9;
        }

        .error {
            background: #fdeaea;
            color: #a12626;
            border: 1px solid #efb5b5;
        }

        /* =========================
           ORDER CARD
        ========================= */

        .order-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-header h2 {
            font-size: 20px;
        }

        .order-date {
            color: #777;
            font-size: 14px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 25px;
            margin-bottom: 20px;
        }

        .info-box {
            line-height: 1.6;
        }

        .info-box strong {
            display: inline-block;
            margin-right: 5px;
        }

        .address {
            grid-column: 1 / -1;
        }

        /* =========================
           STATUS
        ========================= */

        .status-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 15px;
        }

        .status {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .processing {
            background: #cfe2ff;
            color: #084298;
        }

        .delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        /* =========================
           TOTAL
        ========================= */

        .total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total strong {
            font-size: 18px;
        }

        /* =========================
           CANCEL BUTTON
        ========================= */

        .cancel-form {
            margin-top: 20px;
        }

        .cancel-btn {
            background: #c0392b;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .cancel-btn:hover {
            background: #a93226;
        }

        /* =========================
           EMPTY
        ========================= */

        .empty {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 600px) {

            header {
                padding: 12px 20px;
            }

            .site-logo {
                height: 45px;
            }

            .nav-links {
                gap: 12px;
            }

            .nav-links a {
                font-size: 13px;
            }

            .container {
                margin-top: 25px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .address {
                grid-column: auto;
            }

            .total {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

    </style>

</head>

<body>

<header>

    <div class="navbar">

        <div class="logo">
            <a href="index.php">
                <img
                    src="assets/images/Charuta_Logo.png"
                    alt="Charuta"
                    class="site-logo"
                >
            </a>
        </div>

        <div class="nav-links">

            <a href="index.php">Home</a>

            <a href="products.php">Products</a>

            <a href="cart.php">Cart</a>

            <a href="orders.php">My Orders</a>

        </div>

    </div>

</header>


<main class="container">

    <h1>My Orders</h1>


    <!-- Success message after placing order -->

    <?php if (isset($_GET["success"])): ?>

        <div class="message success">
            Your order has been placed successfully.
        </div>

    <?php endif; ?>


    <!-- Success message after cancellation -->

    <?php if (isset($_GET["cancelled"])): ?>

        <div class="message success">
            Your order has been cancelled successfully and the product stock has been restored.
        </div>

    <?php endif; ?>


    <!-- Error message -->

    <?php if (isset($_GET["error"])): ?>

        <div class="message error">
            <?= htmlspecialchars($_GET["error"]) ?>
        </div>

    <?php endif; ?>


    <?php if (empty($orders)): ?>

        <div class="empty">
            <p>You have not placed any orders yet.</p>
        </div>

    <?php else: ?>


        <?php foreach ($orders as $order): ?>

            <?php

            $statusClass = strtolower(
                str_replace(" ", "-", $order["order_status"])
            );

            $paymentStatusClass = strtolower(
                str_replace(" ", "-", $order["payment_status"])
            );

            ?>

            <div class="order-card">

                <!-- Order Header -->

                <div class="order-header">

                    <h2>
                        Order #<?= (int) $order["id"] ?>
                    </h2>

                    <div class="order-date">
                        <?= htmlspecialchars(
                            date(
                                "d M Y, h:i A",
                                strtotime($order["created_at"])
                            )
                        ) ?>
                    </div>

                </div>


                <!-- Delivery Information -->

                <div class="info-grid">

                    <div class="info-box">
                        <strong>Name:</strong>
                        <?= htmlspecialchars($order["delivery_name"]) ?>
                    </div>

                    <div class="info-box">
                        <strong>Phone:</strong>
                        <?= htmlspecialchars($order["phone"]) ?>
                    </div>

                    <div class="info-box address">
                        <strong>Address:</strong>
                        <?= nl2br(htmlspecialchars($order["address"])) ?>
                    </div>

                    <div class="info-box">
                        <strong>Payment:</strong>
                        <?= htmlspecialchars($order["payment_method"]) ?>
                    </div>

                </div>


                <!-- Status -->

                <div class="status-row">

                    <span class="status <?= htmlspecialchars($paymentStatusClass) ?>">
                        Payment: <?= htmlspecialchars($order["payment_status"]) ?>
                    </span>

                    <span class="status <?= htmlspecialchars($statusClass) ?>">
                        Order: <?= htmlspecialchars($order["order_status"]) ?>
                    </span>

                </div>


                <!-- Total -->

                <div class="total">

                    <span>
                        Total
                    </span>

                    <strong>
                        ৳<?= number_format(
                            (float) $order["total_amount"],
                            2
                        ) ?>
                    </strong>

                </div>


                <!-- Cancel Button -->

                <?php if ($order["order_status"] === "Pending"): ?>

                    <form
                        action="cancel_order.php"
                        method="POST"
                        class="cancel-form"
                        onsubmit="return confirm('Are you sure you want to cancel this order?');"
                    >

                        <input
                            type="hidden"
                            name="order_id"
                            value="<?= (int) $order["id"] ?>"
                        >

                        <button
                            type="submit"
                            class="cancel-btn"
                        >
                            Cancel Order
                        </button>

                    </form>

                <?php endif; ?>


            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

</body>

</html>