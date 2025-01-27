<?php
// Start the session
session_start();

// Check if user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <img src="pblogo.png" alt="Your Image" class="top-left-image">
</head>
<body>
    <header>
        <h1>Welcome to Your Dashboard, <?php echo htmlspecialchars($user['name']); ?></h1>
        <nav>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main>
        <?php if ($user_type == 'graduate'): ?>
            <section class="dashboard-section">
                <h2>Your Graduate Dashboard</h2>
                <p>Here you can view available jobs and apply to them!</p>
                <a href="view_jobs.php" class="button">View Available Jobs</a>
            </section>
        <?php elseif ($user_type == 'company'): ?>
            <section class="dashboard-section">
                <h2>Your Company Dashboard</h2>
                <p>Here you can manage your job postings and view applications from graduates!</p>
                <a href="post_job.php" class="button">Post New Job</a>
                <a href="view_applications.php" class="button">View Applications</a>
                <a href="manage_jobs.php" class="button">Manage Job Postings</a>
            </section>
        <?php elseif ($user_type == 'admin'): ?>
            <section class="dashboard-section">
                <h2>Your Admin Dashboard</h2>
                <p>As an admin, you can manage all users and job postings.</p>
                <a href="manage_users.php" class="button">Manage Users</a>
                <a href="manage_jobs.php" class="button">Manage Job Postings</a>
                <a href="view_applications.php" class="button">View Applications</a>
            </section>
        <?php else: ?>
            <p>Invalid user type. Please contact support.</p>
        <?php endif; ?>
    </main>
</body>
</html>
