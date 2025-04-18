<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if job ID is provided
    if (!isset($_GET['job_id']) || !is_numeric($_GET['job_id'])) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job_id = $_GET['job_id'];
    
    // Get job details using prepared statement
    $job_sql = "SELECT * FROM jobs WHERE job_ID = ?";
    $stmt = $conn->prepare($job_sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job_result = $stmt->get_result();
    
    if ($job_result->num_rows == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job = $job_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/view_applications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Job Applications</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message <?php echo htmlspecialchars($_GET['messageType']); ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="job-info">
            <div class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></div>
            <div class="job-meta">
                <span>Category: <?php echo htmlspecialchars($job['job_Category']); ?></span>
                <span>Vacancies: <?php echo htmlspecialchars($job['job_Vacancy']); ?></span>
                <span>Posted: <?php echo date('M d, Y', strtotime($job['job_Offered'])); ?></span>
            </div>
        </div>
        
        <?php
            // Check if job_applications table exists
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
            
            if (mysqli_num_rows($table_check) > 0) {
                // Get applications for this job using prepared statement
                $app_sql = "SELECT a.*, u.name, u.email 
                           FROM job_applications a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.job_id = ? 
                           ORDER BY a.application_date DESC";
                $app_stmt = $conn->prepare($app_sql);
                $app_stmt->bind_param("i", $job_id);
                $app_stmt->execute();
                $app_result = $app_stmt->get_result();
                
                if ($app_result && $app_result->num_rows > 0) {
                    echo "<table class='applications-table'>
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Email</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
                    
                    while ($app = $app_result->fetch_assoc()) {
                        $status_class = strtolower($app['status']);
                        
                        echo "<tr>
                            <td>" . htmlspecialchars($app['name']) . "</td>
                            <td>" . htmlspecialchars($app['email']) . "</td>
                            <td>" . date('M d, Y', strtotime($app['application_date'])) . "</td>
                            <td><span class='status " . $status_class . "'>" . ucfirst($app['status']) . "</span></td>
                            <td class='action-links'>
                                <a href='view_application.php?id=" . $app['id'] . "'><i class='fas fa-eye'></i> View Details</a>";
                        
                        if ($app['status'] == 'pending') {
                            echo "<a href='respond_application.php?id=" . $app['id'] . "&action=accept'><i class='fas fa-check'></i> Accept</a>
                                  <a href='respond_application.php?id=" . $app['id'] . "&action=decline'><i class='fas fa-times'></i> Decline</a>";
                        }
                        
                        echo "</td></tr>";
                    }
                    
                    echo "</tbody></table>";
                } else {
                    echo "<div class='no-applications'>
                        <p>No applications have been received for this job yet.</p>
                    </div>";
                }
            } else {
                echo "<div class='no-applications'>
                    <p>The application system is not yet set up. No applications can be viewed at this time.</p>
                </div>";
            }
        ?>
        
        <div class="back-link">
            <a href="manage_jobs.php"><i class="fas fa-arrow-left"></i> Back to Manage Jobs</a>
        </div>
    </div>
</body>
</html>