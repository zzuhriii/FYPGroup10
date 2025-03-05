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
$sql = "SELECT name, email, phone, ic_number, profile_pic, cv FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Set profile picture or placeholder
$profile_pic = !empty($user['profile_pic']) ? "uploads/profile/" . $user['profile_pic'] : "/Website/media/placeholder.png";
$cv = !empty($user['cv']) ? "uploads/cv/" . $user['cv'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/css/profile.css">
</head>
<body>

    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
            </div>
        <h1>Politeknik Brunei - Update Profile</h1>
        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">
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
                <label for="ic_number">IC Number:</label>
                <input type="text" id="ic_number" name="ic_number" value="<?php echo htmlspecialchars($user['ic_number']); ?>" required>
            </div>

            <div class="input-field">
                <label for="profile_pic">Upload New Profile Picture:</label>
                <input type="file" name="profile_pic" id="profile_pic">
            </div>

            <div class="input-field">
                <label for="cv">Upload New CV (PDF or DOCX):</label>
                <input type="file" name="cv" id="cv" accept=".pdf, .docx">
            </div>

            <?php if ($cv): ?>
                <div class="input-field">
                    <label for="current_cv">Current CV:</label>
                    <span><?php echo htmlspecialchars(basename($cv)); ?></span>
                    <br>
                    <button type="submit" name="delete_cv" value="1">Delete CV</button>
                    <br>
                    <!-- Preview the CV -->
                    <?php
                    $file_extension = pathinfo($cv, PATHINFO_EXTENSION);
                    if (in_array($file_extension, ['pdf', 'docx'])) {
                        // Make sure the link to preview the CV is correct
                        echo "<a href='/Website/user_profile/$cv' target='_blank'>Preview CV</a>";
                    }
                    ?>
                </div>
            <?php else: ?>
                <p>No CV uploaded yet. Please upload your CV.</p>
            <?php endif; ?>

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
