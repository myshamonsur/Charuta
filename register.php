<?php

require_once __DIR__ . "/includes/db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Basic validation
    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $messageType = "error";

    } else {

        try {

            // Check if email already exists
            $stmt = $pdo->prepare("
                SELECT id
                FROM users
                WHERE email = ?
                LIMIT 1
            ");

            $stmt->execute([$email]);

            if ($stmt->fetch()) {

                $message = "This email is already registered. Please login.";
                $messageType = "error";

            } else {

                // Hash password
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert new user
                $stmt = $pdo->prepare("
                    INSERT INTO users
                    (name, email, password)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
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
            $messageType = "error";
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

</head>

<body>

    <div class="auth-container">

        <div class="auth-box">

            <img
                src="assets/images/Charuta_Logo.png"
                alt="Charuta Logo"
                class="auth-logo"
            >

            <h2>Create Account</h2>

            <p class="auth-subtitle">
                Register to continue with Charuta
            </p>

            <?php if ($message !== ""): ?>

                <div
                    class="message <?php echo htmlspecialchars($messageType); ?>"
                >
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="register.php">

                <div class="form-group">

                    <label for="name">
                        Full Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($name ?? ""); ?>"
                        placeholder="Enter your full name"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email ?? ""); ?>"
                        placeholder="Enter your email"
                        required
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
                        placeholder="Enter your password"
                        minlength="6"
                        required
                    >

                </div>

                <button
                    type="submit"
                    class="auth-button"
                >
                    Register
                </button>

            </form>

            <p class="auth-footer">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </p>

        </div>

    </div>

</body>

</html>