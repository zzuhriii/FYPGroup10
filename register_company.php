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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $user_type = 'company'; // Hardcoding the user_type for company

    // Check if the email already exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Error: The email address is already registered. Please use a different email.";
    } else {
        // Insert new user into the database
        $sql = "INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $user_type);

        if ($stmt->execute()) {
            echo "Registration successful! Welcome, Company!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register as Company</title>
    <link rel="stylesheet" href="css/register.css">
    <img src="pblogo.png" alt="Your Image" class="top-left-image">
</head>
<body>
    <header>
        <h1>Register as Company</h1>
    </header>

    <main>
        <form action="register_company.php" method="POST">
            <input type="text" name="name" placeholder="Company Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </main>
</body>
</html>
