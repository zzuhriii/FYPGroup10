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
    <style>
        .tabs {
            display: flex;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-bottom: none;
            background: #f1f1f1;
            margin-right: 5px;
        }
        .tab.active {
            background: white;
            font-weight: bold;
        }
        .tab-content {
            border: 1px solid #ccc;
            padding: 20px;
            background: white;
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
    <script>
        function showTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');

            // Remove "active" class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Add "active" class to the clicked tab
            document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
    </script>
</head>
<body>

    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
            </div>
            <h1>Politeknik Brunei - Update Profile</h1>
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">
        </div>
    </header>

    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="showTab('personal-details')">Personal Details</div>
            <div class="tab" onclick="showTab('achievements')">Achievements</div>
            <div class="tab" onclick="showTab('education')">Education Background</div>
            <div class="tab" onclick="showTab('work-experience')">Past Work Experience</div>
        </div>

        <div id="personal-details" class="tab-content active">
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
                <div class="input-field button-group">
    <input type="submit" value="Update Profile">
    <a href="/Website/main/dashboard.php" class="btn">Return to Dashboard</a>
</div>

<style>
    .button-group {
        display: flex;
        gap: 10px; /* Space between buttons */
        align-items: center;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
    }
    .btn:hover {
        background-color: #0056b3;
    }
</style>

            </form>
        </div>

        <div id="achievements" class="tab-content">
    <h2>Achievements</h2>

    <div class="input-field">
        <label for="achievement_name">Name of Achievement:</label>
        <input type="text" id="achievement_name" name="achievement_name">
    </div>
    <div class="input-field">
        <label for="achievement_date">Date of Achievement:</label>
        <input type="date" id="achievement_date" name="achievement_date">
    </div>
    <div class="input-field">
        <label for="proof_of_achievement">Upload Proof of Achievement (If applicable):</label>
        <input type="file" name="proof_of_achievement" id="proof_of_achievement" accept=".pdf, .jpg, .jpeg, .png">
    </div>
</div>


        <div id="education" class="tab-content">
            <h2>Education Background</h2>
            <form action="update_education.php" method="post">
                <div class="input-field">
                    <label for="education_level">Education Level:</label>
                    <select id="education_level" name="education_level">
                        <option value="O'levels">O'levels</option>
                        <option value="A'levels">A'levels</option>
                        <option value="Diploma">Diploma</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="input-field">
                    <label for="institution">Institution:</label>
                    <input type="text" id="institution" name="institution" required>
                </div>
                <div class="input-field">
                    <label for="results">Results:</label>
                    <input type="text" id="results" name="results" required>
                </div>
            </form>
        </div>

        <div id="work-experience" class="tab-content">
            <h2>Past Work Experience</h2>
            <form action="update_work_experience.php" method="post">
                <div class="input-field">
                    <label for="institution">Name of Institution:</label>
                    <input type="text" id="institution" name="institution" required>
                </div>
                <div class="input-field">
                    <label for="start_date">Beginning of Employment:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="input-field">
                    <label for="end_date">End of Employment:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                <div class="input-field">
                    <label for="position">Description of Position:</label>
                    <textarea id="position" name="position" required></textarea>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
