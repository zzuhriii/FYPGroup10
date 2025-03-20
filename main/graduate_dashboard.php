<?php
// Start the session
session_start();

// Check if user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Database connection
require_once '../includes/db.php';

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


$user_type = $user['user_type'];
$profile_pic = !empty($user['profile_pic']) ? "/Website/user_profile/uploads/profile/" . $user['profile_pic'] : "/Website/user_profile/uploads/profile/placeholder.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Dashboard</title>
    <link rel="stylesheet" href="/Website/assets/css/dashboard.css">
    <link rel="stylesheet" href="/Website/assets/css/dashboard-animations.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
<header>
    <div class="header-content">
        <div class="logo-container">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Logo" class="top-left-image">
            <h1>Welcome to Politeknik Brunei, <?php echo htmlspecialchars($user['name']); ?></h1>
        </div>
        <nav>
            <!-- Profile Picture with Dropdown, shown only for graduates -->
            <?php if ($user_type == 'graduate'): ?>
                <div class="profile-container">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic" id="profilePic">
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="/Website/user_profile/profile.php">View Profile</a>
                        <a href="/Website/authentication/logout.php">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Logout button shown only for company users -->
            <?php if ($user_type == 'company'): ?>
                <div class="logout-container">
                    <a href="/Website/authentication/logout.php" class="logout-btn">Logout</a>
                </div>
            <?php endif; ?>
        </nav>
    </div>
</header>

    <main>
       


            <section class="dashboard-section graduate-dashboard">
                <h2>Your Graduate Dashboard</h2>
                <p>Discover job opportunities tailored to your skills and qualifications. Take the first step towards your dream career today!</p>
                <div class="button-container">
                    <a href="/Website/search_jobs/graduates_homepage.php" class="button staggered-animation">View Available Jobs</a>
                    
                </div>
            </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Politeknik Brunei.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Profile picture click handler
            document.getElementById('profilePic').addEventListener('click', function() {
                var dropdownMenu = document.getElementById('dropdownMenu');
                dropdownMenu.style.display = (dropdownMenu.style.display === 'block') ? 'none' : 'block';
            });

            // Close the dropdown if the user clicks outside
            window.onclick = function(event) {
                if (!event.target.matches('.profile-pic')) {
                    var dropdownMenu = document.getElementById('dropdownMenu');
                    if (dropdownMenu.style.display === 'block') {
                        dropdownMenu.style.display = 'none';
                    }
                }
            };
        });
    </script>
</body>
</html>
