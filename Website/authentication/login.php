<?php
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

session_start(); // Start the session to store login information

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to find the user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables for the logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect based on user type (graduated or company)
            header("Location:/Website/main/dashboard.php"); // Redirect to a dashboard page (you can create this)
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/Website/css/login.css">
    <link rel="stylesheet" href="/Website/css/animations.css">
</head>
<body>
    <header class="animate-fadeIn">
        <h1>Welcome to the Login Page!</h1>
        <img src="/Website/media/pblogo.png" alt="Your Image" class="top-left-image animate-fadeIn">
    </header>

    <main class="animate-slideUp">
        <form action="login.php" method="POST" class="animate-fadeInDelay">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" class="animate-fadeInDelay">Login</button>
        </form>

        <p class="animate-fadeInDelay">Don't have an account? <a href="register_graduate.php">Register as Graduate</a> | <a href="register_company.php">Register as Company</a></p>
    </main>
</body>
</html>

