<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

$message = "";
$loginSuccess = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            /*
             * Regenerate the session ID after successful login.
             */
            session_regenerate_id(true);

            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            /*
             * Save the session data immediately.
             */
            session_write_close();

            /*
             * Redirect after successful login.
             */
            header("Location: index.php");
            exit;

        } else {

            $message = "Invalid email or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | Charuta</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <style>

        .auth-container {
            max-width: 450px;
            margin: 60px auto;
            padding: 20px;
        }

        .auth-card {
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        .auth-card h1 {
            text-align: center;
            color: #713d56;
            margin-bottom: 25px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .auth-logo img {
            width: 130px;
            max-width: 100%;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #713d56;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d9b6c6;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .auth-card .btn {
            width: 100%;
            margin-top: 10px;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            background: #f9eaf0;
            border-radius: 6px;
            color: #713d56;
            text-align: center;
        }

        .auth-link {
            text-align: center;
            margin-top: 20px;
        }

        .auth-link a {
            color: #a64d78;
            text-decoration: none;
            font-weight: 600;
        }

    </style>

</head>

<body>

<header>

    <a href="index.php" class="brand-logo">

        <img
            src="assets/images/Charuta_Logo.png"
            alt="Charuta Beauty and Skincare"
        >

    </a>

    <nav>

        <a href="index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="cart.php">
                Cart
            </a>

            <a href="orders.php">
                My Orders
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        <?php endif; ?>

    </nav>

</header>

<main>

    <section class="auth-container">

        <div class="auth-card">

            <div class="auth-logo">

                <img
                    src="assets/images/Charuta_Logo.png"
                    alt="Charuta Logo"
                >

            </div>

            <h1>
                Login
            </h1>

            <?php if ($message !== ""): ?>

                <div class="message">

                    <?php

                    echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>

                </div>

            <?php endif; ?>

            <form
                method="POST"
                action="login.php"
            >

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        value="<?php

                            echo htmlspecialchars(
                                $_POST["email"] ?? "",
                                ENT_QUOTES,
                                "UTF-8"
                            );

                        ?>"
                    >

                </div>

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="btn"
                >
                    Login
                </button>

            </form>

            <div class="auth-link">

                <p>

                    Don't have an account?

                    <a href="register.php">
                        Register here
                    </a>

                </p>

            </div>

        </div>

    </section>

</main>

<footer>

    <p>

        &copy; <?php echo date("Y"); ?>

        Charuta. All rights reserved.

    </p>

</footer>

</body>

</html>