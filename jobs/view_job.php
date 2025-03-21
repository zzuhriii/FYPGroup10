<?php
    include 'header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if job ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: graduates_homepage.php");
        exit();
    }
    
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get job details
    $job_sql = "SELECT * FROM jobs WHERE job_ID = '$job_id'";
    $job_result = mysqli_query($conn, $job_sql);
    
    if (mysqli_num_rows($job_result) == 0) {
        header("Location: graduates_homepage.php");
        exit();
    }
    
    $job = mysqli_fetch_assoc($job_result);
    
    // Set default company name
    $company_name = "Company";
    
    // Check if application has already been submitted
    $has_applied = false;
    $application_status = '';
    $decline_reason = '';
    
    // Check if job_applications table exists
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
    if (mysqli_num_rows($table_check) > 0) {
        $check_sql = "SELECT id, status, decline_reason FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
        $check_result = mysqli_query($conn, $check_sql);
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $application_data = mysqli_fetch_assoc($check_result);
            $has_applied = true;
            $application_status = $application_data['status'];
            $decline_reason = isset($application_data['decline_reason']) ? $application_data['decline_reason'] : '';
        }
    }
    
    // Handle application submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
        // Check if job_applications table exists, create if not
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
        if (mysqli_num_rows($table_check) == 0) {
            $create_table_sql = "CREATE TABLE job_applications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_id INT NOT NULL,
                user_id INT NOT NULL,
                cover_letter TEXT,
                application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
                feedback TEXT
            )";
            mysqli_query($conn, $create_table_sql);
        }
        
        // Get form data
        $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);
        
        // Insert application
        $insert_sql = "INSERT INTO job_applications (job_id, user_id, cover_letter, application_date) 
                      VALUES ('$job_id', '$user_id', '$cover_letter', NOW())";
        
        if (mysqli_query($conn, $insert_sql)) {
            $message = "Your application has been submitted successfully!";
            $messageType = "success";
            $has_applied = true;
        } else {
            $message = "Error submitting application: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['job_Title']); ?> - Job Details</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/view_job.css">


</head>
<body>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="job-header">
            <h1 class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></h1>
            <div class="job-company"><?php echo htmlspecialchars($company_name); ?></div>
            <div class="job-meta">
                <div class="job-meta-item">
                    <strong>Category:</strong> &nbsp;<?php echo htmlspecialchars($job['job_Category']); ?>
                </div>
                <div class="job-meta-item">
                    <strong>Vacancies:</strong> &nbsp;<?php echo htmlspecialchars($job['job_Vacancy']); ?>
                </div>
                <div class="job-meta-item">
                    <strong>Salary:</strong> &nbsp;<?php echo !empty($job['salary_estimation']) ? htmlspecialchars($job['salary_estimation']) . ' per month' : 'Not specified'; ?>
                </div>
                <div class="job-meta-item">
                    <strong>Posted:</strong> &nbsp;<?php 
                        // More robust date handling
                        $date = $job['job_Offered'];
                        if (!empty($date) && $date != '0000-00-00' && $date != '0000-00-00 00:00:00') {
                            $timestamp = strtotime($date);
                            if ($timestamp && $timestamp > 0 && date('Y', $timestamp) > 1970) {
                                echo date('F d, Y', $timestamp);
                            } else {
                                echo date('F d, Y'); // Current date as fallback
                            }
                        } else {
                            echo date('F d, Y'); // Current date as fallback
                        }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="job-description">
            <h3>Job Description</h3>
            <?php echo nl2br(htmlspecialchars($job['job_Description'])); ?>
        </div>
        
        <?php if ($has_applied): ?>
            <div class="already-applied <?php echo ($application_status == 'rejected' || $application_status == 'declined') ? 'status-rejected' : 'status-' . $application_status; ?>">
                <?php if ($application_status == 'accepted'): ?>
                    <h3>Congratulations! Your application has been accepted</h3>
                    <p>The company will contact you soon with further details.</p>
                <?php elseif ($application_status == 'rejected' || $application_status == 'declined'): ?>
                    <h3>Your application has been declined</h3>
                    <p>Thank you for your interest. We encourage you to apply for other positions that match your skills.</p>
                    <?php if (!empty($decline_reason)): ?>
                        <div class="decline-reason">
                            <h4>Reason:</h4>
                            <p><?php echo nl2br(htmlspecialchars($decline_reason)); ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <h3>Your application is under review</h3>
                    <p>Your application has been submitted and is being reviewed by the company.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="application-form">
                <h3>Apply for this Position</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="cover_letter">Cover Letter / Why you're interested in this position:</label>
                        <textarea id="cover_letter" name="cover_letter" required></textarea>
                    </div>
                    <button type="submit" name="apply" class="submit-btn">Submit Application</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="graduates_homepage.php">Back to Job Listings</a>
        </div>
    </div>
</body>
</html>