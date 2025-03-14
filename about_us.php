<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "company_profile";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest profile data
$sql = "SELECT * FROM company_profile ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    $row = []; // Default empty data
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1, h2 {
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        header {
            background-color: #3498db;
            color: white;
            padding: 20px 0;
            text-align: center;
            border-bottom: 5px solid #2980b9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        header button {
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }

        header button:hover {
            background-color: #34495e;
        }

        /* About Us Section */
        .about-us-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .about-us-section img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
            border-top: 5px solid #3498db;
        }

        footer p {
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            header h1 {
                margin-bottom: 10px;
            }

            header button {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <h1>About Us</h1>
        <div>
            <a href="CompanyProfile.php"><button>Back to Home</button></a>
        </div>
    </header>

    <div class="container">
        <!-- About Us Section -->
        <div class="about-us-section">
            <h2>About Us</h2>
            <img src="data:image/png;base64,<?= base64_encode($row['office_photo']) ?>" alt="Office/Team Photo">
            <p><?= $row['about_us'] ?? 'This is the About Us page. You can add more content here.' ?></p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Your Company Name. All rights reserved.</p>
    </footer>

</body>
</html>