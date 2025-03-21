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
    
    $job_id = mysqli_real_escape_string($conn, $_GET['job_id']);
    
    // Get job details
    $job_sql = "SELECT * FROM jobs WHERE job_ID = '$job_id'";
    $job_result = mysqli_query($conn, $job_sql);
    
    if (mysqli_num_rows($job_result) == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job = mysqli_fetch_assoc($job_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applications - Politeknik Brunei Marketing Day</title>
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
            margin-bottom: 20px;
            color: #333;
        }
        
        .job-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #4285f4;
        }
        
        .job-title {
            font-size: 1.6em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            color: #555;
            font-size: 0.95em;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .applications-table td {
            padding: 14px 15px;
            text-align: left;
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
    </style>
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Job Applications</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message <?php echo $_GET['messageType']; ?>">
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
                // Get applications for this job
                $app_sql = "SELECT a.*, u.name, u.email 
                           FROM job_applications a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.job_id = '$job_id' 
                           ORDER BY a.application_date DESC";
                $app_result = mysqli_query($conn, $app_sql);
                
                if ($app_result && mysqli_num_rows($app_result) > 0) {
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
                    
                    while ($app = mysqli_fetch_assoc($app_result)) {
                        $status_class = strtolower($app['status']);
                        
                        echo "<tr>
                            <td>".htmlspecialchars($app['name'])."</td>
                            <td>".htmlspecialchars($app['email'])."</td>
                            <td>".date('M d, Y', strtotime($app['application_date']))."</td>
                            <td><span class='status ".$status_class."'>".ucfirst($app['status'])."</span></td>
                            <td class='action-links'>
                                <a href='view_application.php?id=".$app['id']."'>View Details</a>";
                        
                        if ($app['status'] == 'pending') {
                            echo "<a href='respond_application.php?id=".$app['id']."&action=accept'>Accept</a>
                                  <a href='respond_application.php?id=".$app['id']."&action=decline'>Decline</a>";
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
            <a href="manage_jobs.php">Back to Manage Jobs</a>
        </div>
    </div>
</body>
</html>