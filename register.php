<?php

require_once __DIR__ . "/includes/session.php";
require_once __DIR__ . "/includes/db.php";

$message = "";
$messageType = "";

$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill in all fields.";
        $messageType = "";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $messageType = "";

    } else {

        try {

            // Check whether email already exists
            $checkStmt = $pdo->prepare(
                "SELECT id FROM users WHERE email = ? LIMIT 1"
            );

            $checkStmt->execute([$email]);

            if ($checkStmt->fetch()) {

                $message = "An account with this email already exists.";
                $messageType = "";

            } else {

                // Hash password
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert new user
                $insertStmt = $pdo->prepare(
                    "INSERT INTO users (name, email, password)
                     VALUES (?, ?, ?)"
                );

                $insertStmt->execute([
                    $name,
                    $email,
                    $hashedPassword
                ]);

                $message = "Registration successful! You can now login.";
                $messageType = "success";

                // Clear form values
                $name = "";
                $email = "";
            }

        } catch (PDOException $e) {

            $message = "Registration failed. Please try again.";
            $messageType = "";
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

    <title>Register - Charuta</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

<header>

    <a href="index.php" class="brand-logo">

        <img
            src="assets/images/Charuta_Logo.png"
            alt="Charuta Logo"
        >

    </a>

    <nav>

        <a href="index.php">Home</a>

        <a href="products.php">Products</a>

        <a href="login.php">Login</a>

    </nav>

</header>


<section class="auth-section">

    <div class="auth-card">

        <h2>Create Account</h2>

        <p class="auth-subtitle">
            Register to continue with Charuta
        </p>


        <?php if ($message !== ""): ?>

            <div class="message <?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="register.php">

            <label for="name">
                Full Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?php echo htmlspecialchars($name); ?>"
                placeholder="Enter your full name"
                required
            >


            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your email"
                required
            >


            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Minimum 6 characters"
                minlength="6"
                required
            >


            <button
                type="submit"
                class="btn form-btn"
            >
                Register
            </button>

        </form>


        <p class="auth-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</section>


<footer>

    <p>
        &copy; <?php echo date("Y"); ?> Charuta. All rights reserved.
    </p>

</footer>

</body>

</html>