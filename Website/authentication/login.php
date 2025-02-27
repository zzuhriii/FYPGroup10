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
    $email_or_ic = $_POST['email_or_ic'];
    $password = $_POST['password'];

    // Query to find the user by either IC number or email
    if (filter_var($email_or_ic, FILTER_VALIDATE_EMAIL)) {
        // If the input is an email, search by email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email_or_ic);
    } else {
        // Otherwise, treat it as an IC number
        $sql = "SELECT * FROM users WHERE ic_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email_or_ic);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
  
        if (password_verify($password, $user['password'])) {
          
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type']; 

           
            if ($user['user_type'] == 'graduate') {
                header("Location: /Website/main/dashboard.php"); 
            } else {
                header("Location: /Website/main/dashboard.php"); 
            }
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that email or IC number.";
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
            <input type="text" name="email_or_ic" placeholder="Email or IC Number" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit" class="animate-fadeInDelay">Login</button>
        </form>

        <p class="animate-fadeInDelay">Don't have an account? <a href="register_graduate.php">Register as Graduate</a> | <a href="register_company.php">Register as Company</a></p>
    </main>
</body>
</html>
