
<?php
session_start();
require_once "includes/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        $message = "Please enter your email and password.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users WHERE email = ?"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_role"] = $user["role"];

            $message = "Login successful! Welcome, " .
                htmlspecialchars($user["name"], ENT_QUOTES, "UTF-8");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Charuta</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .site-logo {
            height: 50px;
            width: auto;
            max-width: 160px;
            object-fit: contain;
            display: block;
        }

        header .logo {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">
        <img
            src="assets/images/Charuta_Logo.png"
            alt="Charuta Logo"
            class="site-logo"
        >
    </a>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main class="auth-section">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="section-description">Login to your Charuta account.</p>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?php echo $message; ?>
            </p>
        <?php endif; ?>

        <?php if (!isset($_SESSION["user_id"])): ?>
            <form method="POST" action="">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

                <button type="submit" class="btn form-btn">Login</button>
            </form>

            <p class="auth-link">
                Don't have an account?
                <a href="register.php">Register here</a>
            </p>
        <?php else: ?>
            <p>You are already logged in.</p>
            <a href="index.php" class="btn">Go to Home</a>
            <a href="logout.php" class="auth-text-link">Logout</a>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Charuta. All rights reserved.</p>
</footer>

</body>
</html>