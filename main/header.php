<?php
// Start the session
session_start();

// Check if user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Database connection
require_once '/Website/includes/db.php';

// Get user details
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$user_type = $user['user_type'];
$profile_pic = !empty($user['profile_pic']) ? "/Website/user_profile/uploads/" . $user['profile_pic'] : "/Website/media/placeholder.png";
?>

<header>
    <div class="header-content">
        <div class="logo-container">
            <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
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
