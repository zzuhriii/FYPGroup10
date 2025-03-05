<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

            // Generate dummy email based on IC number
            $email = $ic_number . "@graduate.pbu.edu.bn";

            // Check if IC number already exists
            $check_sql = "SELECT * FROM users WHERE ic_number = ? OR email = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $ic_number, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>alert('IC Number or Email already registered!');</script>";
            } else {
                $sql = "INSERT INTO users (ic_number, email, name, password, user_type) VALUES (?, ?, ?, ?, 'graduate')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $ic_number, $email, $name, $hashed_password);
                
                if ($stmt->execute()) {
                    echo "<script>alert('Registration successful!'); window.location.href = '/Website/index.php';</script>";
                } else {
                    echo "<script>alert('Error: " . $stmt->error . "');</script>";
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
                    echo "<script>alert('Registration successful!'); window.location.href = 'login.php';</script>";
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            /* Politeknik Brunei Color Scheme */
            --pb-yellow: #FFD700;
            --pb-blue: #006F9C; /* Blue instead of green */
            --pb-dark-blue: #0A1C4B;
            --pb-light-yellow: #FFF3B0;
            --pb-accent: #1A2D5A; /* Dark blue accent */
            --pb-light: #F5F5F5;
            --pb-dark: #333333;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--pb-blue), var(--pb-dark-blue));
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }
        
        .header-container {
            width: 100%;
            max-width: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .header-container img {
            max-width: 80px;
            margin-right: 15px;
        }
        
        .header-container h1 {
            font-size: 1.6rem;
            text-align: center;
            color: var(--pb-blue);
            font-weight: bold;
        }
        
        .main-container {
            background: #fff;
            color: var(--pb-dark);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            border-top: 5px solid var(--pb-yellow);
            margin-bottom: 20px;
        }
        
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .tabs button {
            background: var(--pb-blue);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            margin: 0 5px;
            font-size: 1rem;
            min-width: 120px;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .tabs button:hover {
            background: var(--pb-dark-blue);
        }
        
        .tabs button.active {
            background: var(--pb-accent);
            border-bottom: 3px solid var(--pb-yellow);
        }
        
        form {
            display: none;
            width: 100%;
            background-color: var(--pb-light);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: var(--pb-accent);
        }
        
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus {
            border-color: var(--pb-yellow);
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background: var(--pb-blue);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            background: var(--pb-dark-blue);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
            color: var(--pb-dark);
        }
        
        .login-link a {
            color: var(--pb-blue);
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: var(--pb-dark-blue);
            text-decoration: underline;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--pb-light-yellow);
        }
        
        /* Decorative elements */
        .pb-decoration {
            position: absolute;
            width: 50px;
            height: 50px;
            background-color: var(--pb-yellow);
            opacity: 0.1;
            border-radius: 50%;
            z-index: -1;
        }
        
        .pb-decoration:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
        }
        
        .pb-decoration:nth-child(2) {
            bottom: 15%;
            right: 5%;
            width: 120px;
            height: 120px;
        }
        
        .pb-decoration:nth-child(3) {
            top: 40%;
            right: 20%;
            width: 80px;
            height: 80px;
        }
        
        @media screen and (max-width: 768px) {
            .header-container {
                flex-direction: column;
                padding: 10px;
            }
            
            .header-container img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .main-container {
                padding: 1.5rem;
            }
        }
        
        @media screen and (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header-container h1 {
                font-size: 1.3rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .tabs button {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                min-width: 100px;
            }
            
            form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Decorative elements -->
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>
    <div class="pb-decoration"></div>

    <header class="header-container">
        <img src="/Website/media/pblogo.png" alt="Politeknik Brunei Logo">
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
