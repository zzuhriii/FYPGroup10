<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if application ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: my_applications.php");
        exit();
    }
    
    $application_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get application details with job information
    $app_sql = "SELECT a.*, j.job_Title, j.job_Category, j.job_Description, c.name as company_name 
               FROM job_applications a 
               JOIN jobs j ON a.job_id = j.job_ID 
               JOIN users c ON j.company_id = c.id
               WHERE a.id = '$application_id' AND a.user_id = '$user_id'";
    
    // If company_id column doesn't exist in jobs table, use this alternative query
    if (mysqli_query($conn, "SHOW COLUMNS FROM jobs LIKE 'company_id'")->num_rows == 0) {
        $app_sql = "SELECT a.*, j.job_Title, j.job_Category, j.job_Description, 'Company' as company_name 
                   FROM job_applications a 
                   JOIN jobs j ON a.job_id = j.job_ID 
                   WHERE a.id = '$application_id' AND a.user_id = '$user_id'";
    }
    
    $app_result = mysqli_query($conn, $app_sql);
    
    if (mysqli_num_rows($app_result) == 0) {
        header("Location: my_applications.php");
        exit();
    }
    
    $app = mysqli_fetch_assoc($app_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        .application-header {
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
            margin-bottom: 10px;
            color: #333;
        }
        
        .company-name {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 15px;
        }
        
        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
            color: #555;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            margin-top: 10px;
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
        
        .section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .job-description, .cover-letter {
            white-space: pre-line;
            line-height: 1.6;
            color: #333;
        }
        
        .status-message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
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
    <div class="container">
        <h1>Application Details</h1>
        
        <?php if ($app['status'] == 'accepted'): ?>
        <div class="status-message status-accepted">
            <p><strong>Congratulations!</strong> Your application has been accepted by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>They will contact you soon with further details about the next steps.</p>
        </div>
        <?php elseif ($app['status'] == 'declined'): ?>
        <div class="status-message status-declined">
            <p>We're sorry, but your application has been declined by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>Don't be discouraged - keep applying to other opportunities that match your skills and interests.</p>
        </div>
        <?php else: ?>
        <div class="status-message status-pending">
            <p>Your application is currently under review by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>You will be notified when there is an update to your application status.</p>
        </div>
        <?php endif; ?>
        
        <div class="application-header">
            <div class="job-title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
            <div class="company-name"><?php echo htmlspecialchars($app['company_name']); ?></div>
            <div class="meta-info">
                <span>Category: <?php echo htmlspecialchars($app['job_Category']); ?></span>
                <span>Applied: <?php echo date('M d, Y', strtotime($app['application_date'])); ?></span>
            </div>
            <span class="status <?php echo strtolower($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span>
        </div>
        
        <div class="section">
            <div class="section-title">Job Description</div>
            <div class="job-description">
                <?php echo nl2br(htmlspecialchars($app['job_Description'])); ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Your Cover Letter</div>
            <div class="cover-letter">
                <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
            </div>
        </div>
        
        <div class="back-link">
            <a href="my_applications.php">Back to My Applications</a>
        </div>
    </div>
</body>
</html>