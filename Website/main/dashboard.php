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
$profile_pic = !empty($user['profile_pic']) ? "/Website/user_profile/uploads/" . $user['profile_pic'] : "/Website/media/placeholder.png";
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

        .profile-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.1); 
            opacity: 0.8;
        }

        .profile-container::after {
            content: "Click to view options";
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #888;
            margin-top: 5px;
            display: none;
        }

        .profile-container:hover::after {
            display: block;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 90px;
            right: 0;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 180px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #333;
        }

        .dropdown-menu a:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
                <h1>Welcome to Politeknik Brunei, <?php echo htmlspecialchars($user['name']); ?></h1>
            </div>
            <nav>
                <!-- Profile Picture with Dropdown -->
                <div class="profile-container">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic" id="profilePic">
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="/Website/user_profile/profile.php">View Profile</a>
                        <a href="/Website/authentication/logout.php">Logout</a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <div class="welcome-text">
            <h2>Find Your Perfect Career Match</h2>
            <p>Join our platform where opportunities meet talent. Whether you're a recent graduate looking to kickstart your career or a company seeking fresh talent, we've got you covered.</p>
        </div>

        <?php if ($user_type == 'graduate'): ?>
            <section class="dashboard-section graduate-dashboard">
                <h2>Your Graduate Dashboard</h2>
                <p>Discover job opportunities tailored to your skills and qualifications. Take the first step towards your dream career today!</p>
                <div class="button-container">
                    <a href="/Website/search_jobs/graduates_homepage.php" class="button staggered-animation">View Available Jobs</a>
                    <a href="/Website/user_profile/profile.php" class="button staggered-animation">View Your Profile</a>
                </div>
            </section>
        <?php elseif ($user_type == 'company'): ?>
            <section class="dashboard-section company-dashboard">
                <h2>Your Company Dashboard</h2>
                <p>Connect with top talent from Politeknik. Post jobs, review applications, and find the perfect candidates for your team.</p>
                <div class="button-container">
                    <a href="/Website/jobs/add_job.php" class="button staggered-animation">Post New Job</a>
                    <a href="/Website/FindingApplicants.php" class="button staggered-animation">View Applications</a>
                    <a href="/Website/jobs/manage_jobs.php" class="button staggered-animation">Manage Job Postings</a>
                </div>
            </section>
        <?php elseif ($user_type == 'admin'): ?>
            <section class="dashboard-section admin-dashboard">
                <h2>Your Admin Dashboard</h2>
                <p>Manage the entire platform efficiently. Oversee users, job postings, and applications to ensure everything runs smoothly.</p>
                <div class="button-container">
                    <a href="manage_users.php" class="button staggered-animation">Manage Users</a>
                    <a href="manage_jobs.php" class="button staggered-animation">Manage Job Postings</a>
                    <a href="view_applications.php" class="button staggered-animation">View Applications</a>
                </div>
            </section>
        <?php else: ?>
            <p>Invalid user type. Please contact support.</p>
        <?php endif; ?>
    </main>

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
