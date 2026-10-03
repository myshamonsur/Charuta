<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";


// User must be logged in

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}


$userId = (int) $_SESSION["user_id"];


// Get user's orders

$stmt = $pdo->prepare("
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

$stmt->execute([$userId]);

$orders = $stmt->fetchAll(
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

    <title>My Orders - Charuta</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .site-logo {
            height: 50px;
            width: auto;
            display: block;
        }


        .orders-section {
            padding: 50px 20px;
        }

        .orders-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .orders-container h1 {
            margin-bottom: 30px;
        }


        .success-message {
            background: #e8f7e8;
            color: #216b21;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .error-message {
            background: #fdeaea;
            color: #a12626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }


        .order-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-id {
            font-size: 18px;
            font-weight: 700;
        }

        .order-date {
            color: #777;
            font-size: 14px;
        }


        .order-info {
            margin-top: 20px;
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
        }

        .info-label {
            width: 160px;
            font-weight: 600;
        }

        .info-value {
            flex: 1;
        }


        .status-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }


        .order-total {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            font-size: 20px;
            font-weight: 700;
        }


        .cancel-order {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .cancel-btn {
            background: #c0392b;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .cancel-btn:hover {
            background: #a93226;
        }


        .empty-orders {
            background: #ffffff;
            padding: 40px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .empty-orders p {
            color: #777;
            margin-bottom: 20px;
        }

        .btn-link {
            display: inline-block;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 7px;
        }


        @media (max-width: 600px) {

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-row {
                display: block;
            }

            .info-label {
                width: auto;
                margin-bottom: 3px;
            }

            .order-total {
                font-size: 17px;
            }

            .site-logo {
                height: 45px;
            }

        }

    </style>

</head>


<body>


<header>

    <div class="container">

        <div class="logo">

            <a href="index.php">

                <img
                    src="assets/images/Charuta_Logo.png"
                    alt="Charuta"
                    class="site-logo"
                >

            </a>

        </div>


        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a href="orders.php">
                Orders
            </a>

            <a href="logout.php">
                Logout
            </a>

        </nav>

    </div>

</header>


<section class="orders-section">

    <div class="orders-container">


        <h1>
            My Orders
        </h1>


        <?php if (isset($_GET["success"])): ?>

            <div class="success-message">

                Your order has been placed successfully!

            </div>

        <?php endif; ?>


        <?php if (isset($_GET["cancelled"])): ?>

            <div class="success-message">

                Your order has been cancelled successfully
                and the product stock has been restored.

            </div>

        <?php endif; ?>


        <?php if (isset($_GET["error"])): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars(
                    $_GET["error"],
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if (!$orders): ?>


            <div class="empty-orders">

                <h2>
                    No Orders Yet
                </h2>

                <p>
                    You have not placed any orders yet.
                </p>

                <a
                    href="products.php"
                    class="btn btn-link"
                >
                    Start Shopping
                </a>

            </div>


        <?php else: ?>


            <?php foreach ($orders as $order): ?>


                <div class="order-card">


                    <div class="order-header">

                        <div class="order-id">

                            Order #

                            <?php
                            echo (int)$order["id"];
                            ?>

                        </div>


                        <div class="order-date">

                            <?php

                            echo htmlspecialchars(
                                date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                ),
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </div>

                    </div>


                    <div class="order-info">


                        <div class="info-row">

                            <div class="info-label">
                                Name:
                            </div>

                            <div class="info-value">

                                <?php
                                echo htmlspecialchars(
                                    $order["delivery_name"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </div>

                        </div>


                        <div class="info-row">

                            <div class="info-label">
                                Phone:
                            </div>

                            <div class="info-value">

                                <?php
                                echo htmlspecialchars(
                                    $order["phone"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </div>

                        </div>


                        <div class="info-row">

                            <div class="info-label">
                                Delivery Address:
                            </div>

                            <div class="info-value">

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $order["address"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                );
                                ?>

                            </div>

                        </div>


                        <div class="info-row">

                            <div class="info-label">
                                Payment Method:
                            </div>

                            <div class="info-value">

                                <?php
                                echo htmlspecialchars(
                                    $order["payment_method"],
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>

                            </div>

                        </div>


                    </div>


                    <?php

                    $paymentStatus =
                        strtolower(
                            trim(
                                $order["payment_status"]
                            )
                        );

                    $orderStatus =
                        strtolower(
                            trim(
                                $order["order_status"]
                            )
                        );

                    ?>


                    <div class="status-row">


                        <span
                            class="status
                            <?php
                            echo $paymentStatus === "paid"
                                ? "status-paid"
                                : "status-pending";
                            ?>"
                        >

                            Payment:

                            <?php
                            echo htmlspecialchars(
                                $order["payment_status"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </span>


                        <span
                            class="status
                            <?php

                            if (
                                $orderStatus === "delivered"
                            ) {

                                echo "status-delivered";

                            } elseif (
                                $orderStatus === "processing"
                            ) {

                                echo "status-processing";

                            } elseif (
                                $orderStatus === "cancelled"
                            ) {

                                echo "status-cancelled";

                            } else {

                                echo "status-pending";

                            }

                            ?>"
                        >

                            Order:

                            <?php
                            echo htmlspecialchars(
                                $order["order_status"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </span>


                    </div>


                    <div class="order-total">

                        <span>
                            Total
                        </span>

                        <span>

                            ৳<?php

                            echo number_format(
                                (float)$order["total_amount"],
                                2
                            );

                            ?>

                        </span>

                    </div>


                    <?php

                    if (
                        strcasecmp(
                            trim($order["order_status"]),
                            "Pending"
                        ) === 0
                    ):

                    ?>

                        <div class="cancel-order">

                            <form
                                action="cancel_order.php"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to cancel this order?');"
                            >

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?php
                                        echo (int)$order["id"];
                                    ?>"
                                >

                                <button
                                    type="submit"
                                    class="cancel-btn"
                                >
                                    Cancel Order
                                </button>

                            </form>

                        </div>

                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</section>


</body>

</html>