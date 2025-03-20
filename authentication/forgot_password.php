<?php
// Start the session
session_start();

// Include database connection
require_once '../includes/db.php';
require_once '../includes/email_helper.php';

// Initialize variables
$message = '';
$messageType = '';
$type = isset($_GET['type']) ? $_GET['type'] : 'graduate';
$identifier_field = ($type == 'graduate') ? 'IC Number' : 'Email';
$table = ($type == 'graduate') ? 'users' : 'companies';
$identifier_column = ($type == 'graduate') ? 'ic_number' : 'email';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'];
    $email = $_POST['email'];
    
    // Validate input
    if (empty($identifier) || empty($email)) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } else {
        // Check if user exists
        $sql = "SELECT id, email FROM $table WHERE $identifier_column = ? AND email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $identifier, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Store OTP in database
            $sql = "UPDATE $table SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $otp, $otp_expiry, $user['id']);
            $stmt->execute();
            
            // Prepare email content
            $to = $email;
            $subject = "Password Reset OTP - Politeknik Brunei Marketing Day";
            $message_body = "
            <html>
            <head>
                <title>Password Reset OTP</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    h2 { color: #4285f4; }
                    .otp { font-size: 24px; font-weight: bold; color: #4285f4; padding: 10px; background-color: #f5f5f5; display: inline-block; }
                    .footer { margin-top: 30px; font-size: 12px; color: #777; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Password Reset Request</h2>
                    <p>You have requested to reset your password for the Politeknik Brunei Marketing Day platform.</p>
                    <p>Your One-Time Password (OTP) is: <span class='otp'>$otp</span></p>
                    <p>This OTP will expire in 15 minutes.</p>
                    <p>If you did not request this password reset, please ignore this email.</p>
                    <p>Thank you,<br>Politeknik Brunei Marketing Day Team</p>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this message.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Send email with OTP
            $email_result = sendEmail($to, $subject, $message_body);
            
            if ($email_result === true) {
                // Store email in session for the next step
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_type'] = $type;
                
                // Redirect to OTP verification page
                header("Location: verify_otp.php");
                exit();
            } else {
                // For development/testing purposes, show the OTP on screen if email fails
                $message = "Email could not be sent. For testing purposes, your OTP is: $otp <br><a href='verify_otp.php'>Click here to enter your OTP and reset your password</a>";
                $messageType = "success";
                
                // Store email in session for the next step
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_type'] = $type;
            }
        } else {
            $message = "No account found with the provided details.";
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
    <title>Forgot Password - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/forgot_password.css">
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        
        <div class="tabs">
            <button id="graduate-tab" onclick="location.href='forgot_password.php?type=graduate'" <?php echo $type == 'graduate' ? 'class="active"' : ''; ?>>Graduate</button>
            <button id="company-tab" onclick="location.href='forgot_password.php?type=company'" <?php echo $type == 'company' ? 'class="active"' : ''; ?>>Company</button>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="identifier"><?php echo $identifier_field; ?>:</label>
                <input type="text" id="identifier" name="identifier" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <button type="submit">Send Reset OTP</button>
        </form>
        
        <div class="back-link">
            <a href="/Website/index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>