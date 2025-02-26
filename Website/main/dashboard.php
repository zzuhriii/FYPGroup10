<?php
// Start the session
session_start();

// Check if user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

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

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user_type = $user['user_type'];

// Fetch profile picture or use default
$profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "/Website/media/defaultpfp.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Dashboard</title>
    <link rel="stylesheet" href="/Website/css/dashboard.css">
    <link rel="stylesheet" href="/Website/css/dashboard-animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #0056b3;
            color: white;
        }

        .profile-logout-container {
            display: flex;
            align-items: center;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin-right: 10px;
        }

        .logout-btn {
            background-color: red;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Welcome to Politeknik Brunei, <?php echo htmlspecialchars(string: $user['name']); ?></h1>
            <nav class="profile-logout-container">
                <img src="<?php echo htmlspecialchars(string: $profile_pic); ?>" alt="Profile Picture" class="profile-pic">
                <a href="/Website/authentication/logout.php" class="logout-btn">Logout</a>
            </nav>
        </div>
    </header>
</body>
</html>
