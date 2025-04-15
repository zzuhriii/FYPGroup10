<?php
// Database connection
require_once '../includes/db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $register_type = $_POST['register_type'];
    $name = trim($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required!');</script>";
    } elseif ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($register_type == 'graduate') {
            $ic_number = trim($_POST['ic_number']);
            $email = trim($_POST['email']); // Get email from form instead of generating it

            // Check if IC number already exists
            $check_sql = "SELECT * FROM users WHERE ic_number = ? OR email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $ic_number, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>alert('IC Number or Email already registered!');</script>";
            } else {
                $sql = "INSERT INTO users (ic_number, email, name, password, user_type, email_locked) VALUES (?, ?, ?, ?, 'graduate', 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $ic_number, $email, $name, $hashed_password);
                
                if ($stmt->execute()) {
                    // Registration successful
                    
                    // Send welcome email
                    require_once '../includes/mailer.php';
                    sendWelcomeEmail($email, $name, 'graduate');
                    
                    // Redirect to success page or login page
                    $_SESSION['registration_success'] = true;
                    header("Location: /Website/index.php");
                    exit();
                }
            }
        } else {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Invalid email format!');</script>";
                exit;
            }

            // Check if email already exists
            $check_sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>alert('Email already registered!');</script>";
            } else {
                $sql = "INSERT INTO users (email, name, password, user_type) VALUES (?, ?, ?, 'company')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $email, $name, $hashed_password);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Registration successful!'); window.location.href = '/Website/index.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $stmt->error . "');</script>";
                }
            }
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/register.css">
    <style>
        .form-text {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>

    <header class="header-container">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo">
        <h1>Politeknik Brunei Marketing Day Registration</h1>
    </header>

    <div class="main-container">
        <div class="tabs">
            <button id="graduate-tab" onclick="showTab('graduate')">Graduate</button>
            <button id="company-tab" onclick="showTab('company')">Company</button>
        </div>

        <!-- Graduate Form -->
        <form id="graduate-form" action="register.php" method="POST">
            <input type="hidden" name="register_type" value="graduate">
            
            <div class="form-group">
                <label for="graduate-ic">IC Number</label>
                <input type="text" id="graduate-ic" name="ic_number" placeholder="Enter your IC Number" required>
                <small class="form-text">Note: You won't be able to change this IC number later.</small>
                
            </div>
            
            <div class="form-group">
                <label for="graduate-email">Email Address</label>
                <input type="email" id="graduate-email" name="email" placeholder="Enter your email address" required>
                <small class="form-text">Note: You won't be able to change this email later.</small>
            </div>
            
            <div class="form-group">
                <label for="graduate-name">Full Name</label>
                <input type="text" id="graduate-name" name="name" placeholder="Enter your name" required>
            </div>
            
            <div class="form-group">
                <label for="graduate-password">Password</label>
                <input type="password" id="graduate-password" name="password" placeholder="Enter a password" required>
            </div>
            
            <div class="form-group">
                <label for="graduate-confirm-password">Confirm Password</label>
                <input type="password" id="graduate-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            
            <button type="submit">Register</button>
        </form>

        <!-- Company Form -->
        <form id="company-form" action="register.php" method="POST">
            <input type="hidden" name="register_type" value="company">
            
            <div class="form-group">
                <label for="company-email">Company Email</label>
                <input type="email" id="company-email" name="email" placeholder="Enter company email" required>
            </div>
            
            <div class="form-group">
                <label for="company-name">Company Name</label>
                <input type="text" id="company-name" name="name" placeholder="Enter company name" required>
            </div>
            
            <div class="form-group">
                <label for="company-password">Password</label>
                <input type="password" id="company-password" name="password" placeholder="Enter a password" required>
            </div>
            
            <div class="form-group">
                <label for="company-confirm-password">Confirm Password</label>
                <input type="password" id="company-confirm-password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="login-link">
            <p>Already have an account? <a href="/Website/index.php">Login here</a></p>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Politeknik Brunei Marketing Day. All Rights Reserved.</p>
    </footer>

    <script>
        function showTab(tab) {
            if (tab == 'graduate') {
                document.getElementById('graduate-form').style.display = 'block';
                document.getElementById('company-form').style.display = 'none';
                document.getElementById('graduate-tab').classList.add('active');
                document.getElementById('company-tab').classList.remove('active');
            } else {
                document.getElementById('graduate-form').style.display = 'none';
                document.getElementById('company-form').style.display = 'block';
                document.getElementById('company-tab').classList.add('active');
                document.getElementById('graduate-tab').classList.remove('active');
            }
        }
        
        // Default Tab
        showTab('graduate');
    </script>
</body>
</html>
