<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page - Politeknik Brunei</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #005f73;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin: 0;
            font-size: 2.5em;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .input-field {
            margin-bottom: 20px;
        }

        .input-field label {
            font-weight: bold;
            font-size: 1.1em;
            margin-bottom: 5px;
            display: block;
        }

        .input-field input[type="text"],
        .input-field input[type="email"],
        .input-field input[type="tel"],
        .input-field input[type="file"] {
            width: 100%;
            padding: 12px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            margin-top: 5px;
        }

        .input-field input[type="submit"] {
            padding: 12px 20px;
            background-color: #005f73;
            color: white;
            font-size: 1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        .input-field input[type="submit"]:hover {
            background-color: #003e4d;
        }

        .back-button {
            padding: 12px 20px;
            background-color: #0a2a3f;
            color: white;
            font-size: 1em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            margin-top: 20px;
        }

        .back-button:hover {
            background-color: #05354b;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 10px;
            background-color: #0a2a3f;
            color: white;
        }

        .footer p {
            margin: 0;
            font-size: 1em;
        }
    </style>
</head>
<body>

    <header>
        <h1>Politeknik Brunei - Update Profile</h1>
    </header>

    <div class="container">
        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">

            <div class="input-field">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="input-field">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="input-field">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <div class="input-field">
                <label for="profile_pic">Upload New Profile Picture:</label>
                <input type="file" name="profile_pic" id="profile_pic">
            </div>

            <div class="input-field">
                <input type="submit" value="Update Profile">
            </div>
        </form>

        <button class="back-button" onclick="window.location.href='/Website/main/dashboard.php'">Back to Dashboard</button>
    </div>

    <div class="footer">
        <p>&copy; 2025 Politeknik Brunei</p>
    </div>

</body>
</html>
