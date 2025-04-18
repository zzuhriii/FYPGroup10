<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if job_applications table has a status column
    $columns_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'status'");
    $has_status_column = mysqli_num_rows($columns_check) > 0;
    
    // If status column doesn't exist, we need to add it
    if (!$has_status_column) {
        mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/my_applications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>My Job Applications</h1>
        
        <?php
            // Check if job_applications table exists
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
            
            if (mysqli_num_rows($table_check) > 0) {
                // Check if company_id column exists in jobs table
                $company_id_check = mysqli_query($conn, "SHOW COLUMNS FROM jobs LIKE 'company_id'");
                $has_company_id = mysqli_num_rows($company_id_check) > 0;
                
                // Prepare the SQL query based on table structure
                if ($has_company_id) {
                    $app_sql = "SELECT a.*, j.job_Title, j.job_Category, c.name as company_name 
                               FROM job_applications a 
                               JOIN jobs j ON a.job_id = j.job_ID 
                               JOIN users c ON j.company_id = c.id
                               WHERE a.user_id = ?
                               ORDER BY a.application_date DESC";
                    
                    $stmt = $conn->prepare($app_sql);
                    $stmt->bind_param("i", $user_id);
                } else {
                    $app_sql = "SELECT a.*, j.job_Title, j.job_Category, 'Company' as company_name 
                               FROM job_applications a 
                               JOIN jobs j ON a.job_id = j.job_ID 
                               WHERE a.user_id = ?
                               ORDER BY a.application_date DESC";
                    
                    $stmt = $conn->prepare($app_sql);
                    $stmt->bind_param("i", $user_id);
                }
                
                $stmt->execute();
                $app_result = $stmt->get_result();
                
                if ($app_result && $app_result->num_rows > 0) {
                    // Check if there are any accepted applications
                    $has_accepted = false;
                    $temp_result = $app_result->data_seek(0);
                    while ($temp = $app_result->fetch_assoc()) {
                        if ($temp['status'] == 'accepted') {
                            $has_accepted = true;
                            break;
                        }
                    }
                    
                    // Reset result pointer
                    $app_result->data_seek(0);
                    
                    // Show congratulations message if there are accepted applications
                    if ($has_accepted) {
                        echo "<div class='message success'>
                            <p><strong>Congratulations!</strong> One or more of your applications have been accepted. Check the status below.</p>
                        </div>";
                    }
                    
                    echo "<table class='applications-table'>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Company</th>
                                <th>Category</th>
                                <th>Applied On</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";
                    
                    while ($app = $app_result->fetch_assoc()) {
                        // Make sure status has a valid value
                        if (empty($app['status'])) {
                            $app['status'] = 'pending';
                        }
                        
                        $status_class = strtolower($app['status']);
                        $status_text = ucfirst($app['status']);
                        
                        echo "<tr>
                            <td>" . htmlspecialchars($app['job_Title']) . "</td>
                            <td>" . htmlspecialchars($app['company_name']) . "</td>
                            <td>" . htmlspecialchars($app['job_Category']) . "</td>
                            <td>" . date('M d, Y', strtotime($app['application_date'])) . "</td>
                            <td><span class='status " . $status_class . "'>" . $status_text . "</span></td>
                            <td class='action-links'>
                                <a href='view_application_details.php?id=" . $app['id'] . "'><i class='fas fa-eye'></i> View Details</a>
                            </td>
                        </tr>";
                    }
                    
                    echo "</tbody></table>";
                } else {
                    echo "<div class='no-applications'>
                        <p>You haven't applied to any jobs yet.</p>
                        <p>Browse available jobs and submit your applications.</p>
                    </div>";
                }
            } else {
                echo "<div class='no-applications'>
                    <p>The application system is not yet set up. Please check back later.</p>
                </div>";
            }
        ?>
        
        <div class="back-link">
            <a href="/Website/jobs/browse_jobs.php"><i class="fas fa-search"></i> Browse Available Jobs</a>
        </div>
    </div>
</body>
</html>