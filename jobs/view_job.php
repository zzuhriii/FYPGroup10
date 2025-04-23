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
                status ENUM('pending', 'viewed', 'interviewed', 'accepted', 'declined') DEFAULT 'pending',
                feedback TEXT,
                queue_position INT,
                email_sent TINYINT(1) DEFAULT 0
            )";
            mysqli_query($conn, $create_table_sql);
        } else {
            // Check if queue_position column exists, add if not
            $column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'queue_position'");
            if (mysqli_num_rows($column_check) == 0) {
                mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN queue_position INT");
            }
            
            // Check if email_sent column exists, add if not
            $column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'email_sent'");
            if (mysqli_num_rows($column_check) == 0) {
                mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN email_sent TINYINT(1) DEFAULT 0");
            }
        }
        
        // Get form data
        $cover_letter = mysqli_real_escape_string($conn, $_POST['cover_letter']);
        
        // Get the current highest queue position for this job
        $queue_sql = "SELECT MAX(queue_position) as max_position FROM job_applications WHERE job_id = '$job_id'";
        $queue_result = mysqli_query($conn, $queue_sql);
        $queue_data = mysqli_fetch_assoc($queue_result);
        $next_position = ($queue_data['max_position'] ?? 0) + 1;
        
        // Insert application with queue position
        $insert_sql = "INSERT INTO job_applications (job_id, user_id, cover_letter, application_date, queue_position) 
                      VALUES ('$job_id', '$user_id', '$cover_letter', NOW(), '$next_position')";
        
        if (mysqli_query($conn, $insert_sql)) {
            $message = "Your application has been submitted successfully! Your queue number is #$next_position";
            $messageType = "success";
            $has_applied = true;
            $queue_position = $next_position;
            
            // Get user email
            $user_sql = "SELECT email, name FROM users WHERE id = '$user_id'";
            $user_result = mysqli_query($conn, $user_sql);
            $user_data = mysqli_fetch_assoc($user_result);
            $user_email = $user_data['email'] ?? '';
            $user_name = $user_data['name'] ?? 'Applicant';
            
            // Get job title
            $job_title = $job['job_Title'] ?? 'Job Position';
            
            // Send email notification
            if (!empty($user_email)) {
                // Use mailer.php instead of direct mail() function
                require_once '../includes/mailer.php';
                $subject = "Your Application Queue Number for $job_title";
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
                            <p>Thank you for applying to <strong>$job_title</strong>. Your application has been received and is now in our queue system.</p>
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
                
                // Send email using the function from mailer.php
                sendEmail($user_email, $subject, $email_message);
                
                // Update email_sent status
                mysqli_query($conn, "UPDATE job_applications SET email_sent = 1 WHERE job_id = '$job_id' AND user_id = '$user_id'");
            }
        } else {
            $message = "Error submitting application: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
    
    // Get queue position if already applied
    $queue_position = null;
    if ($has_applied) {
        // First check if queue_position column exists
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'queue_position'");
        if (mysqli_num_rows($column_check) == 0) {
            // Add the column if it doesn't exist
            mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN queue_position INT");
            
            // Initialize queue positions for existing applications
            $update_queue = "SET @counter = 0; 
                            UPDATE job_applications 
                            SET queue_position = (@counter:=@counter+1) 
                            WHERE job_id = '$job_id' 
                            ORDER BY application_date ASC";
            mysqli_multi_query($conn, $update_queue);
            
            // Clear results to allow next query
            while (mysqli_next_result($conn)) {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
            }
        }
        
        // Check if email_sent column exists, add if not
        $email_column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'email_sent'");
        if (mysqli_num_rows($email_column_check) == 0) {
            // Add the column if it doesn't exist
            mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN email_sent TINYINT(1) DEFAULT 0");
        }
        
        // Now we can safely query the queue position
        $queue_sql = "SELECT queue_position, status FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
        $queue_result = mysqli_query($conn, $queue_sql);
        if ($queue_result && mysqli_num_rows($queue_result) > 0) {
            $queue_data = mysqli_fetch_assoc($queue_result);
            $queue_position = $queue_data['queue_position'];
            $application_status = $queue_data['status'];
            
            // Check if status has changed to 'viewed' and email hasn't been sent
            $email_check_sql = "SELECT email_sent FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
            $email_check_result = mysqli_query($conn, $email_check_sql);
            $email_sent = mysqli_fetch_assoc($email_check_result)['email_sent'] ?? 0;
            
            if ($application_status == 'viewed' && $email_sent == 0) {
                // Get user email
                $user_sql = "SELECT email, name FROM users WHERE id = '$user_id'";
                $user_result = mysqli_query($conn, $user_sql);
                $user_data = mysqli_fetch_assoc($user_result);
                $user_email = $user_data['email'] ?? '';
                $user_name = $user_data['name'] ?? 'Applicant';
                
                // Get job title
                $job_title = $job['job_Title'] ?? 'Job Position';
                
                // Send email notification
                if (!empty($user_email)) {
                    $to = $user_email;
                    $subject = "Your Application is Being Reviewed - Queue #$queue_position";
                    $email_message = "
                    <html>
                    <head>
                        <title>Application Being Reviewed</title>
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
                                <h1>Your Application is Being Reviewed</h1>
                            </div>
                            <div class='content'>
                                <p>Dear $user_name,</p>
                                <p>Good news! Your application for <strong>$job_title</strong> is now being reviewed.</p>
                                <p>Your queue position is:</p>
                                <div class='queue-number'>#$queue_position</div>
                                <p>The employer is currently reviewing your application. You will be notified of any updates.</p>
                            </div>
                            <div class='footer'>
                                <p>This is an automated message. Please do not reply to this email.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Set email headers
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: Politeknik Brunei <noreply@pb.edu.bn>" . "\r\n";
                    
                    // Send email
                    mail($to, $subject, $email_message, $headers);
                    
                    // Update email_sent status
                    mysqli_query($conn, "UPDATE job_applications SET email_sent = 1 WHERE job_id = '$job_id' AND user_id = '$user_id'");
                }
            }
        }
    }
    
    // Get total applicants for this job
    $total_sql = "SELECT COUNT(*) as total FROM job_applications WHERE job_id = '$job_id'";
    $total_result = mysqli_query($conn, $total_sql);
    $total_applicants = mysqli_fetch_assoc($total_result)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['job_Title']); ?> - Job Details</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/view_job.css">
    <style>
        /* Popup styles */
        .queue-popup {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 15px;
            width: 220px;
            z-index: 1000;
            transition: all 0.3s ease;
            display: none;
        }
        
        .queue-popup.show {
            display: block;
        }
        
        .queue-popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .queue-popup-title {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .queue-popup-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #999;
        }
        
        .queue-popup-number {
            font-size: 36px;
            font-weight: bold;
            color: #4285f4;
            text-align: center;
            margin: 15px 0;
        }
        
        .queue-popup-info {
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        
        .queue-popup-status {
            margin-top: 10px;
            padding: 8px;
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-viewed {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-interviewed {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .queue-popup-minimize {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #4285f4;
            text-align: center;
            width: 100%;
            margin-top: 10px;
            padding: 5px 0;
        }
        
        .queue-popup-minimize:hover {
            text-decoration: underline;
        }
        
        .queue-popup-minimized {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #4285f4;
            color: white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
        }
        
        /* New Queue Tab styles */
        .queue-tab {
            position: fixed;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            background-color: #4285f4;
            color: white;
            padding: 15px 10px;
            border-radius: 8px 0 0 8px;
            box-shadow: -2px 0 8px rgba(0,0,0,0.1);
            cursor: pointer;
            z-index: 999;
            writing-mode: vertical-rl;
            text-orientation: mixed;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
            display: none;
            transition: all 0.3s ease;
        }
        
        .queue-tab:hover {
            padding-right: 15px;
            background-color: #3367d6;
        }
        
        .queue-tab-label {
            transform: rotate(180deg);
        }
    </style>
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
                    <strong>Salary:</strong> &nbsp;<?php echo ($job['min_salary'] && $job['max_salary']) ? 'BND ' . number_format($job['min_salary']) . ' - BND ' . number_format($job['max_salary']) . ' per month' : 'Not specified';  ?>
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
                
                <?php if ($queue_position): ?>
                    <div class="queue-info" style="margin-top: 20px; text-align: center;">
                        <div style="background-color: #f5f5f5; border-radius: 8px; padding: 15px; display: inline-block; min-width: 200px;">
                            <h4 style="margin-top: 0; color: #333;">Your Queue Position</h4>
                            <div style="font-size: 36px; font-weight: bold; color: #4285f4; margin: 10px 0;">#<?php echo $queue_position; ?></div>
                            <p style="margin-bottom: 0; color: #666;">Total applicants: <?php echo $total_applicants; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="application-form">
                <h3>Apply for this Position</h3>
                <p style="color: #666; margin-bottom: 20px;">Current applicants: <?php echo $total_applicants; ?> | Your position will be: #<?php echo $total_applicants + 1; ?></p>
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

</div>
    
    <?php if ($has_applied && $queue_position): ?>
    <!-- Queue Popup -->
    <div class="queue-popup" id="queuePopup">
        <div class="queue-popup-header">
            <span class="queue-popup-title">Your Queue Position</span>
            <button class="queue-popup-close" id="closePopup">&times;</button>
        </div>
        <div class="queue-popup-number">#<?php echo $queue_position; ?></div>
        <div class="queue-popup-info">
            Total applicants: <?php echo $total_applicants; ?>
        </div>
        <div class="queue-popup-status status-<?php echo strtolower($application_status); ?>">
            Status: <?php echo ucfirst($application_status); ?>
        </div>
        <button class="queue-popup-minimize" id="minimizePopup">Minimize</button>
    </div>
    
    <!-- Minimized Queue Popup -->
    <div class="queue-popup-minimized" id="minimizedPopup">
        #<?php echo $queue_position; ?>
    </div>
    
    <!-- Persistent Queue Tab -->
    <div class="queue-tab" id="queueTab">
        <div class="queue-tab-label">Queue #<?php echo $queue_position; ?></div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('queuePopup');
        const minimizedPopup = document.getElementById('minimizedPopup');
        const queueTab = document.getElementById('queueTab');
        const closeBtn = document.getElementById('closePopup');
        const minimizeBtn = document.getElementById('minimizePopup');
        
        // Show tab by default
        queueTab.style.display = 'block';
        
        // Check localStorage to see if user previously had popup open
        if (localStorage.getItem('queuePopupOpen-<?php echo $job_id; ?>-<?php echo $user_id; ?>') === 'true') {
            popup.classList.add('show');
            queueTab.style.display = 'none';
        } else if (localStorage.getItem('queuePopupMinimized-<?php echo $job_id; ?>-<?php echo $user_id; ?>') === 'true') {
            minimizedPopup.style.display = 'flex';
            queueTab.style.display = 'none';
        }
        
        // Tab click opens the full popup
        queueTab.addEventListener('click', function() {
            popup.classList.add('show');
            queueTab.style.display = 'none';
            minimizedPopup.style.display = 'none';
            localStorage.setItem('queuePopupOpen-<?php echo $job_id; ?>-<?php echo $user_id; ?>', 'true');
            localStorage.removeItem('queuePopupMinimized-<?php echo $job_id; ?>-<?php echo $user_id; ?>');
        });
        
        // Close popup
        closeBtn.addEventListener('click', function() {
            popup.classList.remove('show');
            queueTab.style.display = 'block';
            localStorage.removeItem('queuePopupOpen-<?php echo $job_id; ?>-<?php echo $user_id; ?>');
            localStorage.removeItem('queuePopupMinimized-<?php echo $job_id; ?>-<?php echo $user_id; ?>');
        });
        
        // Minimize popup
        minimizeBtn.addEventListener('click', function() {
            popup.classList.remove('show');
            minimizedPopup.style.display = 'flex';
            queueTab.style.display = 'none';
            localStorage.setItem('queuePopupMinimized-<?php echo $job_id; ?>-<?php echo $user_id; ?>', 'true');
            localStorage.removeItem('queuePopupOpen-<?php echo $job_id; ?>-<?php echo $user_id; ?>');
        });
        
        // Show full popup when clicking on minimized version
        minimizedPopup.addEventListener('click', function() {
            minimizedPopup.style.display = 'none';
            popup.classList.add('show');
            queueTab.style.display = 'none';
            localStorage.setItem('queuePopupOpen-<?php echo $job_id; ?>-<?php echo $user_id; ?>', 'true');
            localStorage.removeItem('queuePopupMinimized-<?php echo $job_id; ?>-<?php echo $user_id; ?>');
        });
        
        // Check for status changes every 30 seconds
        setInterval(function() {
            fetch('check_application_status.php?job_id=<?php echo $job_id; ?>&user_id=<?php echo $user_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.status !== '<?php echo $application_status; ?>') {
                        // Status has changed, reload the page
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error checking status:', error));
        }, 30000);
    });
    </script>
    <?php endif; ?>
</body>
</html>
