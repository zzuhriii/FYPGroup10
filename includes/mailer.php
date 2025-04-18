<?php
// First, include the PHPMailer files
require 'D:/xampp/htdocs/Website/vendor/phpmailer/phpmailer/src/Exception.php';
require 'D:/xampp/htdocs/Website/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'D:/xampp/htdocs/Website/vendor/phpmailer/phpmailer/src/SMTP.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Function to send emails using SMTP
function sendEmail($to, $subject, $body, $altBody = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0;                      // Set to 2 for debugging
        $mail->isSMTP();                           // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';      // SMTP server
        $mail->SMTPAuth   = true;                  // Enable SMTP authentication
        $mail->Username   = 'group10fyppb@gmail.com'; // SMTP username
        $mail->Password   = 'fryi tchi dodr uzys';    // SMTP password (app password for Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;                   // TCP port to connect to
        
        // Recipients
        $mail->setFrom('group10fyppb@gmail.com', 'Politeknik Brunei Marketing Day');
        $mail->addAddress($to);                    // Add recipient
        
        // Content
        $mail->isHTML(true);                       // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Function to send interview notification
function sendInterviewNotification($to, $name, $job_title, $queue_position) {
    $subject = "URGENT: You're Being Called Now! - Queue #$queue_position for $job_title";
    
    $body = "
    <html>
    <head>
        <title>Your Queue Number is Being Called NOW</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #ff4b4b; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .queue-number { font-size: 36px; font-weight: bold; color: #ff4b4b; text-align: center; 
                            padding: 20px; margin: 20px 0; background-color: white; border-radius: 8px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
            .urgent { color: #ff4b4b; font-weight: bold; font-size: 18px; }
            .action-required { background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .button { display: inline-block; background-color: #ff4b4b; color: white; padding: 12px 25px; 
                     text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⚠️ YOU ARE BEING CALLED NOW! ⚠️</h1>
            </div>
            <div class='content'>
                <p>Dear $name,</p>
                <p class='urgent'>IMMEDIATE ACTION REQUIRED: You are being called RIGHT NOW for your interview!</p>
                <div class='queue-number'>#$queue_position</div>
                <div class='action-required'>
                    <p><strong>Your application for <span style='color: #ff4b4b;'>$job_title</span> is being called for interview.</strong></p>
                    <p>Please proceed IMMEDIATELY to the employer's booth or interview area. If you're not available within the next few minutes, your position in the queue may be given to the next applicant.</p>
                </div>
                <p>If you are already at the venue, please look for signs directing you to the interview area or ask any staff member for assistance.</p>
                <div style='text-align: center;'>
                    <a href='#' class='button'>I'M ON MY WAY</a>
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>If you have any questions, please contact the event organizers directly.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $body);
}

// Function to send status update email
function sendStatusUpdateEmail($to, $name, $job_title, $status, $decline_reason = '', $queue_position = null) {
    // For viewed status, use the interview notification instead
    if ($status === 'viewed') {
        // Get the queue position from the database if not provided
        global $conn;
        if ($queue_position === null) {
            // Try to find the queue position from job_applications table
            $sql = "SELECT queue_position FROM job_applications 
                    WHERE user_id = (SELECT id FROM users WHERE email = ?) 
                    AND job_id = (SELECT job_ID FROM jobs WHERE job_Title = ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $to, $job_title);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $queue_position = $row['queue_position'];
            } else {
                $queue_position = "N/A"; // Fallback if queue position not found
            }
        }
        return sendInterviewNotification($to, $name, $job_title, $queue_position);
    }
    
    $subject = "Application Status Update - " . ucfirst($status);
    
    $body = "
    <html>
    <head>
        <title>Application Status Update</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: " . ($status === 'accepted' ? '#4CAF50' : ($status === 'declined' ? '#F44336' : '#4285f4')) . "; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Application Status: " . ucfirst($status) . "</h1>
            </div>
            <div class='content'>
                <p>Dear " . $name . ",</p>";
    
    if ($status === 'accepted') {
        $body .= "<p>Congratulations! Your application for <strong>" . $job_title . "</strong> has been accepted.</p>
                  <p>The employer will contact you soon with further details about the next steps in the hiring process.</p>";
    } elseif ($status === 'declined') {
        $body .= "<p>Thank you for your interest in <strong>" . $job_title . "</strong>.</p>
                  <p>We regret to inform you that your application has not been selected to move forward at this time.</p>";
        if (!empty($decline_reason)) {
            $body .= "<p><strong>Feedback:</strong> " . nl2br(htmlspecialchars($decline_reason)) . "</p>";
        }
        $body .= "<p>We encourage you to apply for other positions that match your skills and experience.</p>";
    } elseif ($status === 'interviewed') {
        $body .= "<p>Thank you for interviewing for the <strong>" . $job_title . "</strong> position.</p>
                  <p>Your interview has been recorded, and we will be in touch with you soon regarding the next steps.</p>";
    }
    
    $body .= "</div>
            <div class='footer'>
                <p>This is an automated message. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $body);
}

// Function to send welcome email after registration
function sendWelcomeEmail($to, $name, $userType) {
    $subject = "Welcome to Politeknik Brunei Marketing Day";
    
    // Different welcome messages based on user type
    $userTypeText = ($userType == 'graduate') ? 'Graduate' : 'Company';
    $specificMessage = '';
    
    if ($userType == 'graduate') {
        $specificMessage = "
            <p>As a graduate, you can now:</p>
            <ul>
                <li>Browse job opportunities posted by companies</li>
                <li>Apply for positions that match your skills and interests</li>
                <li>Connect with potential employers</li>
                <li>Showcase your portfolio and achievements</li>
            </ul>
        ";
    } else {
        $specificMessage = "
            <p>As a company, you can now:</p>
            <ul>
                <li>Post job opportunities for Politeknik Brunei graduates</li>
                <li>Browse graduate profiles to find potential candidates</li>
                <li>Connect with talented individuals</li>
                <li>Participate in Politeknik Brunei events</li>
            </ul>
        ";
    }
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4285f4; color: white; padding: 15px 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
            .welcome { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #4285f4; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; padding: 10px; background-color: #f1f1f1; border-radius: 0 0 5px 5px; }
            .button { display: inline-block; background-color: #4285f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to Politeknik Brunei Marketing Day</h2>
            </div>
            <div class='content'>
                <p class='welcome'>Hello " . htmlspecialchars($name) . ",</p>
                <p>Thank you for registering as a " . $userTypeText . " on the Politeknik Brunei Marketing Day platform. Your account has been successfully created!</p>
                
                " . $specificMessage . "
                
                <p>We're excited to have you join our community and look forward to your participation.</p>
                
                <div style='text-align: center;'>
                    <a href='http://localhost/Website/index.php' class='button'>Login to Your Account</a>
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; " . date('Y') . " Politeknik Brunei Marketing Day. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($to, $subject, $body);
}
?>