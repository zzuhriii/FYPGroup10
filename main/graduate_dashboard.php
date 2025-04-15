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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .email-reminder {
            margin-top: 15px;
            padding: 12px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .email-reminder i {
            color: #ffc107;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .email-reminder p {
            margin: 5px 0;
            font-size: 14px;
            color: #856404;
        }
        
        .dashboard-button.secondary {
            background-color: #6c757d;
            margin-top: 8px;
            font-size: 14px;
            padding: 6px 12px;
        }
        
        .dashboard-button.secondary:hover {
            background-color: #5a6268;
        }
    </style>
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
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <h3>Job Opportunities</h3>
                    <p>Explore job listings tailored to your qualifications and interests.</p>
                    <div class="button-container">
                        <a href="/Website/jobs/graduates_homepage.php" class="dashboard-button primary">
                            <i class="fas fa-briefcase"></i> View Available Jobs
                        </a>
                        
                        <?php if (strpos($user['email'], '@graduate.pbu.edu.bn') !== false): ?>
                        <div class="email-reminder">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Please update your email address in your profile for better communication.</p>
                            <a href="/Website/user_profile/profile.php" class="dashboard-button secondary">
                                <i class="fas fa-envelope"></i> Update Email
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
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
