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
    <title><?php echo htmlspecialchars($job['job_Title']); ?> - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/view_job.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Top navigation with logo -->
    <nav class="top-nav">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" class="logo">
        </a>
    </nav>

    <div class="container">
        <h1>Job Details</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Job details card -->
        <div class="job-card">
            <!-- Job header with title and meta information -->
            <div class="job-header">
                <div class="job-title"><?php echo htmlspecialchars($job['job_Title']); ?></div>
                
                <!-- Company information -->
                <?php
                    // Get company information
                    $company_name = "Company";
                    $company_location = "";
                    if (isset($job['company_id'])) {
                        $company_sql = "SELECT name, address FROM users WHERE id = ? AND user_type = 'company'";
                        $company_stmt = $conn->prepare($company_sql);
                        $company_stmt->bind_param("i", $job['company_id']);
                        $company_stmt->execute();
                        $company_result = $company_stmt->get_result();
                        if ($company_result && $company_result->num_rows > 0) {
                            $company_data = $company_result->fetch_assoc();
                            $company_name = $company_data['name'];
                            $company_location = $company_data['address'];
                        }
                    }
                ?>
                <div class="job-company">
                    <div class="company-logo">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="company-info">
                        <div class="company-name"><?php echo htmlspecialchars($company_name); ?></div>
                        <?php if (!empty($company_location)): ?>
                        <div class="company-location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company_location); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Key job details in badges -->
                <div class="job-meta">
                    <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['job_Category']))); ?></span>
                    <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($job['job_Vacancy']); ?> <?php echo $job['job_Vacancy'] > 1 ? 'Positions' : 'Position'; ?></span>
                    <span><i class="fas fa-calendar-alt"></i> Posted: <?php echo date('M d, Y h:i A', strtotime($job['job_Offered'])); ?></span>
                    <?php if (!empty($job['salary_estimation'])): ?>
                    <span><i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($job['salary_estimation']); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($job['application_deadline'])): ?>
                    <span><i class="fas fa-hourglass-end"></i> Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($job['programme'])): ?>
                    <span><i class="fas fa-graduation-cap"></i> Programme: <?php echo htmlspecialchars($job['programme']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Application status display if user has applied -->
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
            
            <!-- Job body with full description -->
            <div class="job-body">
                <div class="job-section">
                    <h3><i class="fas fa-file-alt"></i> Job Description</h3>
                    <div class="job-description">
                        <?php echo nl2br(htmlspecialchars($job['job_Description'])); ?>
                    </div>
                </div>
                
                <!-- Key highlights - displayed in a row of boxes -->
                <?php if (!empty($job['job_Location']) || !empty($job['salary_estimation']) || !empty($job['job_Vacancy'])): ?>
                <div class="job-section">
                    <h3><i class="fas fa-star"></i> Key Details</h3>
                    <div class="key-highlights">
                        <?php if (!empty($job['job_Location'])): ?>
                        <div class="highlight-item">
                            <div class="highlight-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="highlight-title">Location</div>
                            <div class="highlight-value"><?php echo htmlspecialchars($job['job_Location']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($job['salary_estimation'])): ?>
                        <div class="highlight-item">
                            <div class="highlight-icon"><i class="fas fa-dollar-sign"></i></div>
                            <div class="highlight-title">Salary</div>
                            <div class="highlight-value"><?php echo htmlspecialchars($job['salary_estimation']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="highlight-item">
                            <div class="highlight-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="highlight-title">Deadline</div>
                            <div class="highlight-value">
                                <?php echo !empty($job['application_deadline']) ? date('M d, Y', strtotime($job['application_deadline'])) : 'Open until filled'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Application form for graduates who haven't applied yet -->
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
        
        <!-- Back link -->
        <div class="back-link">
            <a href="<?php echo $is_graduate ? '../graduate/my_applications.php' : '../graduate/browse_jobs.php'; ?>">
                <i class="fas fa-<?php echo $is_graduate ? 'list-alt' : 'search'; ?>"></i> 
                <?php echo $is_graduate ? 'View My Applications' : 'Browse More Jobs'; ?>
            </a>
        </div>
    </div>
    
    <!-- Include the universal floating button -->
    <?php include_once '../../includes/floating-button.php'; ?>

    <script>
        // Initialize any JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-scroll to message if present
            const message = document.querySelector('.message');
            if (message) {
                message.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    </script>
</body>
</html>