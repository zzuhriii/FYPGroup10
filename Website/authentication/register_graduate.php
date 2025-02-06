<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $user_type = 'graduate';

    // Validation
    $errors = [];
    
    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters long";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        // Check if email exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered. Please use a different email or login.";
            $messageType = 'error';
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $user_type);

            if ($stmt->execute()) {
                $message = "Registration successful! Please login.";
                $messageType = 'success';
                // Redirect after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $message = "Registration failed. Please try again.";
                $messageType = 'error';
            }
        }
        $stmt->close();
    } else {
        $message = implode("<br>", $errors);
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Graduate</title>
    <link rel="stylesheet" href="/Website/css/register.css">
    <link rel="stylesheet" href="/Website/css/register-animations.css">
    </head>
<body>
    <header>
        <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
        <h1>Register as Graduate</h1>
    </header>

    <main>
        <div class="form-container">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Choose a strong password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Confirm your password">
                </div>

                <button type="submit">Register</button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </main>
</body>
</html>