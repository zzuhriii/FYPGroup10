<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if job ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: browse_jobs.php");
        exit();
    }
    
    $job_id = $_GET['id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    $is_graduate = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'graduate';
    
    // Get job details using prepared statement
    $job_sql = "SELECT * FROM jobs WHERE job_ID = ?";
    $stmt = $conn->prepare($job_sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job_result = $stmt->get_result();
    
    if ($job_result->num_rows == 0) {
        header("Location: browse_jobs.php");
        exit();
    }
    
    $job = $job_result->fetch_assoc();
    
    // Check if the user has already applied for this job
    $has_applied = false;
    $application_status = '';
    
    if ($is_graduate && $user_id) {
        $check_sql = "SELECT * FROM job_applications WHERE job_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $job_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result && $check_result->num_rows > 0) {
            $has_applied = true;
            $application = $check_result->fetch_assoc();
            $application_status = isset($application['status']) ? $application['status'] : 'pending';
        }
    }
    
    // Handle application submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_graduate) {
        if (!$has_applied) {
            $cover_letter = $_POST['cover_letter'];
            
            // Check if application already exists (double check)
            $check_duplicate = "SELECT id FROM job_applications WHERE job_id = ? AND user_id = ?";
            $duplicate_stmt = $conn->prepare($check_duplicate);
            $duplicate_stmt->bind_param("ii", $job_id, $user_id);
            $duplicate_stmt->execute();
            $duplicate_result = $duplicate_stmt->get_result();
            
            if ($duplicate_result->num_rows == 0) {
                // Get the next queue position
                $queue_sql = "SELECT MAX(queue_position) as max_position FROM job_applications WHERE job_id = ?";
                $queue_stmt = $conn->prepare($queue_sql);
                $queue_stmt->bind_param("i", $job_id);
                $queue_stmt->execute();
                $queue_result = $queue_stmt->get_result();
                $queue_data = $queue_result->fetch_assoc();
                $next_position = ($queue_data['max_position'] ? $queue_data['max_position'] + 1 : 1);
                
                // Insert application
                $insert_sql = "INSERT INTO job_applications (job_id, user_id, cover_letter, application_date, status, queue_position) 
                              VALUES (?, ?, ?, NOW(), 'pending', ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iisi", $job_id, $user_id, $cover_letter, $next_position);
                
                if ($insert_stmt->execute()) {
                    // Get user email for notification
                    $user_sql = "SELECT email, name FROM users WHERE id = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("i", $user_id);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    $user_data = $user_result->fetch_assoc();
                    $user_email = $user_data['email'] ?? '';
                    $user_name = $user_data['name'] ?? 'Applicant';
                    
                    // Send email notification if email exists
                    if (!empty($user_email)) {
                        require_once '../../includes/mailer.php';
                        $subject = "Your Application Queue Number for " . $job['job_Title'];
                        $email_message = "
                        <html>
                        <head>
                            <title>Application Queue Number</title>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #4285f4; color: white; padding: 20px; text-align: center; }
                                .content { padding: 20px; background-color: #f9f9f9; }
                                .queue-number { font-size: 36px; font-weight: bold; color: #4285f4; text-align: center; 
                                                padding: 20px; margin: 20px 0; background-color: white; border-radius: 8px; }
                                .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h1>Application Received</h1>
                                </div>
                                <div class='content'>
                                    <p>Dear $user_name,</p>
                                    <p>Thank you for applying to <strong>" . htmlspecialchars($job['job_Title']) . "</strong>. Your application has been received and is now in our queue system.</p>
                                    <p>Your queue position is:</p>
                                    <div class='queue-number'>#$next_position</div>
                                    <p>You will receive another email notification when your application is being reviewed.</p>
                                    <p>Please keep this number for your reference. You can also check your application status anytime by logging into your account.</p>
                                </div>
                                <div class='footer'>
                                    <p>This is an automated message. Please do not reply to this email.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                        ";
                        
                        sendEmail($user_email, $subject, $email_message);
                    }
                    
                    // Redirect to prevent form resubmission
                    header("Location: view_job.php?id=$job_id&applied=success");
                    exit();
                } else {
                    $message = "Error submitting application: " . $conn->error;
                    $messageType = "error";
                }
            } else {
                // Application already exists, redirect to prevent resubmission
                header("Location: view_job.php?id=$job_id&applied=duplicate");
                exit();
            }
        }
    }
    
    // Handle URL parameters after redirect
    if (isset($_GET['applied'])) {
        if ($_GET['applied'] == 'success') {
            $message = "Your application has been submitted successfully!";
            $messageType = "success";
            $has_applied = true;
            $application_status = 'pending';
        } else if ($_GET['applied'] == 'duplicate') {
            $message = "You have already applied for this job.";
            $messageType = "info";
            $has_applied = true;
            $application_status = 'pending';
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
    <link rel="stylesheet" href="/Website/assets/css/view_job.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Job Details</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="job-header">
            <div class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></div>
            <div class="job-meta">
                <span>Category: <?php echo htmlspecialchars($job['job_Category']); ?></span>
                <span>Vacancies: <?php echo htmlspecialchars($job['job_Vacancy']); ?></span>
                <span>Posted: <?php echo date('M d, Y', strtotime($job['job_Offered'])); ?></span>
                <?php if (!empty($job['salary_estimation'])): ?>
                <span>Salary: <?php echo htmlspecialchars($job['salary_estimation']); ?></span>
                <?php endif; ?>
                <?php if (!empty($job['application_deadline'])): ?>
                <span>Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?></span>
                <?php endif; ?>
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
                    <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Application</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="<?php echo $is_graduate ? 'my_applications.php' : 'browse_jobs.php'; ?>">
                <i class="fas fa-<?php echo $is_graduate ? 'list-alt' : 'search'; ?>"></i> 
                <?php echo $is_graduate ? 'View My Applications' : 'Browse More Jobs'; ?>
            </a>
        </div>
    </div>
</body>
</html>