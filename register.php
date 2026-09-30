
<?php
require_once "includes/db.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (strlen($name) > 100) {
        $message = "Name must not exceed 100 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($email) > 150) {
        $message = "Email must not exceed 150 characters.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } else {
        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );
        $check->execute([$email]);

        if ($check->fetch()) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashedPassword
            ]);

            $message = "Registration successful! You can now login.";
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Charuta</title>
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
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="auth-section">
    <div class="auth-card">
        <h2>Create an Account</h2>
        <p class="section-description">
            Join Charuta and explore our skincare products.
        </p>

        <?php if ($message !== ""): ?>
            <p class="message <?php echo $success ? 'success' : ''; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                required
                value="<?php echo htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
            >

            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                maxlength="150"
                required
                value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>"
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="6"
                required
            >

            <button type="submit" class="btn form-btn">Register</button>
        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Charuta. All rights reserved.</p>
</footer>

</body>
</html>