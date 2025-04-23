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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1100px;
            margin: 80px auto 40px;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .message {
            padding: 12px 20px;
            margin-bottom: 25px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .job-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            border: 1px solid #eaeaea;
        }
        
        .job-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 14px;
            color: #6c757d;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border-radius: 6px;
            overflow: hidden;
        }
        
        .applications-table thead {
            background-color: #f8f9fa;
        }
        
        .applications-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eaeaea;
        }
        
        .applications-table td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            vertical-align: middle;
        }
        
        .applications-table tr:last-child td {
            border-bottom: none;
        }
        
        .applications-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }
        
        .pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .declined, .rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .action-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-links a {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            color: white;
            transition: all 0.2s ease;
        }
        
        .action-links a:first-child {
            background-color: #17a2b8;
        }
        
        .action-links a:nth-child(2) {
            background-color: #28a745;
        }
        
        .action-links a:nth-child(3) {
            background-color: #dc3545;
        }
        
        .action-links a i {
            margin-right: 5px;
        }
        
        .action-links a:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .no-applications {
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 6px;
            color: #6c757d;
            border: 1px dashed #dee2e6;
            margin-bottom: 30px;
        }
        
        .back-link {
            margin-top: 20px;
        }
        
        .back-link a {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .back-link a i {
            margin-right: 8px;
        }
        
        .back-link a:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: fixed; top: 15px; left: 15px; z-index: 1000;">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
        </a>
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
                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($job['job_Category']); ?></span>
                <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($job['job_Vacancy']); ?> Vacancies</span>
                <span><i class="fas fa-calendar-alt"></i> Posted: 
                    <?php 
                    if (!empty($job['job_Offered']) && $job['job_Offered'] != '0000-00-00') {
                        $job_date = strtotime($job['job_Offered']);
                        if ($job_date && $job_date > 0) {
                            echo date('F d, Y', $job_date);
                        } else {
                            echo date('F d, Y'); // Current date as fallback
                        }
                    } else {
                        echo date('F d, Y'); // Current date as fallback
                    }
                    ?>
                </span>
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
                            <td>" . date('F d, Y', strtotime($app['application_date'])) . "</td>
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