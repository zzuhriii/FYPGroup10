<?php
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
        $mail->Password   = 'lgmo ekcf eibp mwqs';    // SMTP password (app password for Gmail)
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