<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if job ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: browse_jobs.php");
        exit();
    }
    
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $is_graduate = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'graduate';
    
    // Get job details
    $job_sql = "SELECT * FROM jobs WHERE job_ID = '$job_id'";
    $job_result = mysqli_query($conn, $job_sql);
    
    if (mysqli_num_rows($job_result) == 0) {
        header("Location: browse_jobs.php");
        exit();
    }
    
    $job = mysqli_fetch_assoc($job_result);
    
    // Check if the user has already applied for this job
    $has_applied = false;
    $application_status = '';
    
    if ($is_graduate && $user_id) {
        $check_sql = "SELECT * FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $has_applied = true;
            $application = mysqli_fetch_assoc($check_result);
            $application_status = isset($application['status']) ? $application['status'] : 'pending';
            
            // Debug information
            $debug_info = "Application ID: " . $application['id'] . ", Status: " . $application_status;
        }
    }
    
    // Handle application submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_graduate) {
        if (!$has_applied) {
            $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);
            
            $insert_sql = "INSERT INTO job_applications (job_id, user_id, cover_letter, application_date, status) 
                          VALUES ('$job_id', '$user_id', '$cover_letter', NOW(), 'pending')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $has_applied = true;
                $application_status = 'pending';
                $message = "Your application has been submitted successfully!";
                $messageType = "success";
            } else {
                $message = "Error submitting application: " . mysqli_error($conn);
                $messageType = "error";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['job_Title']); ?> - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .job-header {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #4285f4;
        }
        
        .job-title {
            font-size: 1.8em;
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
        
        .job-description {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .application-form {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .form-title {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 16px;
            height: 200px;
            resize: vertical;
        }
        
        .submit-btn {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s;
            display: block;
            margin: 0 auto;
        }
        
        .submit-btn:hover {
            background-color: #3367d6;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
        
        .application-status {
            text-align: center;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
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
        <h1>Job Details</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="job-header">
            <div class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></div>
            <div class="job-meta">
                <span>Category: <?php echo htmlspecialchars($job['job_Category']); ?></span>
                <span>Vacancies: <?php echo htmlspecialchars($job['job_Vacancy']); ?></span>
                <span>Posted: <?php echo date('M d, Y', strtotime($job['job_Offered'])); ?></span>
            </div>
        </div>
        
        <?php if ($has_applied): ?>
            <?php if ($application_status == 'accepted'): ?>
                <div class="application-status status-accepted">
                    <p><strong>Congratulations!</strong> Your application for this job has been accepted.</p>
                    <p>The company will contact you soon with further details.</p>
                </div>
            <?php elseif ($application_status == 'rejected' || $application_status == 'declined'): ?>
                <div class="application-status status-declined">
                    <p>We're sorry, but your application for this job has been declined.</p>
                    <p>Don't be discouraged - keep applying to other opportunities that match your skills.</p>
                </div>
            <?php else: ?>
                <div class="application-status status-pending">
                    <p>You have already applied for this job.</p>
                    <p>Your application is currently under review. You will be notified when there is an update.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="job-description">
            <?php echo nl2br(htmlspecialchars($job['job_Description'])); ?>
        </div>
        
        <?php if ($is_graduate && !$has_applied): ?>
            <div class="application-form">
                <div class="form-title">Apply for this Job</div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter</label>
                        <textarea id="cover_letter" name="cover_letter" placeholder="Introduce yourself and explain why you're a good fit for this position..." required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Submit Application</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="<?php echo $is_graduate ? 'my_applications.php' : 'browse_jobs.php'; ?>">
                <?php echo $is_graduate ? 'View My Applications' : 'Browse More Jobs'; ?>
            </a>
        </div>
    </div>
</body>
</html>