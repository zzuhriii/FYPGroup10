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
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <img src="/Website/media/pblogo.png" alt="Politeknik Logo" class="top-left-image">
                <h1>Welcome to Politeknik Brunei, <?php echo htmlspecialchars($user['name']); ?></h1>
            </div>
            <nav>
                <a href="/Website/authentication/logout.php" class="logout-btn">Logout</a>
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
                    <a href="/Website/jobs/get_jobs.php" class="button staggered-animation">View Available Jobs</a>
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
        // Add animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('.dashboard-section');
            
            // Add intersection observer to trigger animations when elements come into view
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = "1";
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            sections.forEach(section => {
                observer.observe(section);
            });
        });
    </script>
</body>
</html>