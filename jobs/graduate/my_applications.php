<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Debug: Check if job_applications table has a status column
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
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1, h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .applications-table th, .applications-table td {
            padding: 14px 15px;
            text-align: left;
        }
        
        .applications-table td {
            color: #000;
        }
        
        .applications-table th {
            background-color: #4285f4;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85em;
            letter-spacing: 0.5px;
        }
        
        .applications-table tr {
            border-bottom: 1px solid #eee;
        }
        
        .applications-table tr:last-child {
            border-bottom: none;
        }
        
        .applications-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            min-width: 80px;
        }
        
        .pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .accepted {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .declined {
            background-color: #F8D7DA;
            color: #721C24;
        }
        
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            color: #4285f4;
            font-weight: 500;
            transition: color 0.2s;
            display: inline-block;
            padding: 4px 8px;
        }
        
        .action-links a:hover {
            color: #3367d6;
            text-decoration: underline;
        }
        
        .no-applications {
            text-align: center;
            padding: 40px 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            color: #555;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .no-applications p {
            margin: 10px 0;
            font-size: 1.1em;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        
        .back-link a {
            color: #4285f4;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            padding: 10px 20px;
            border: 1px solid #4285f4;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .back-link a:hover {
            background-color: #4285f4;
            color: white;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #4285f4;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .info {
            background-color: #e1f5fe;
            color: #0c5460;
            border-left: 4px solid #4285f4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Job Applications</h1>
        
        <?php
            // Check if job_applications table exists
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
            
            if (mysqli_num_rows($table_check) > 0) {
                // Get applications for this user
                $app_sql = "SELECT a.*, j.job_Title, j.job_Category, c.name as company_name 
                           FROM job_applications a 
                           JOIN jobs j ON a.job_id = j.job_ID 
                           JOIN users c ON j.company_id = c.id
                           WHERE a.user_id = '$user_id' 
                           ORDER BY a.application_date DESC";
                
                // If company_id column doesn't exist in jobs table, use this alternative query
                if (mysqli_query($conn, "SHOW COLUMNS FROM jobs LIKE 'company_id'")->num_rows == 0) {
                    $app_sql = "SELECT a.*, j.job_Title, j.job_Category, 'Company' as company_name 
                               FROM job_applications a 
                               JOIN jobs j ON a.job_id = j.job_ID 
                               WHERE a.user_id = '$user_id' 
                               ORDER BY a.application_date DESC";
                }
                
                $app_result = mysqli_query($conn, $app_sql);
                
                if ($app_result && mysqli_num_rows($app_result) > 0) {
                    // Check if there are any accepted applications
                    $has_accepted = false;
                    $temp_result = mysqli_query($conn, $app_sql);
                    while ($temp = mysqli_fetch_assoc($temp_result)) {
                        if ($temp['status'] == 'accepted') {
                            $has_accepted = true;
                            break;
                        }
                    }
                    
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
                    
                    while ($app = mysqli_fetch_assoc($app_result)) {
                        // Debug: Print the raw status value to see what's stored in the database
                        // echo "<!-- Debug: Status = " . $app['status'] . " -->";
                        
                        // Make sure status has a valid value
                        if (empty($app['status'])) {
                            $app['status'] = 'pending';
                        }
                        
                        $status_class = strtolower($app['status']);
                        $status_text = ucfirst($app['status']);
                        
                        echo "<tr>
                            <td>".htmlspecialchars($app['job_Title'])."</td>
                            <td>".htmlspecialchars($app['company_name'])."</td>
                            <td>".htmlspecialchars($app['job_Category'])."</td>
                            <td>".date('M d, Y', strtotime($app['application_date']))."</td>
                            <td><span class='status ".$status_class."'>".$status_text."</span></td>
                            <td class='action-links'>
                                <a href='view_application_details.php?id=".$app['id']."'>View Details</a>
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
            <a href="/Website/jobs/browse_jobs.php">Browse Available Jobs</a>
        </div>
    </div>
</body>
</html>