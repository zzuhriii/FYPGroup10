<?php
// Start the session
session_start();

// Include database connection and mailer
require_once '../includes/db.php';
require_once '../includes/mailer.php';

// Include PHPMailer autoloader
require '../vendor/autoload.php';

// Check if type is set
if (!isset($_GET['type']) || ($_GET['type'] != 'graduate' && $_GET['type'] != 'company')) {
    header("Location: /Website/index.php");
    exit();
}

$type = $_GET['type'];
$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Additional field for graduates
    $ic_number = ($type == 'graduate' && isset($_POST['ic_number'])) ? $_POST['ic_number'] : '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif ($type == 'graduate' && empty($ic_number)) {
        $message = "Please enter your IC number.";
        $messageType = "error";
    } else {
        // Check if email exists in the database
        $table = 'users'; // Both graduates and companies are in the users table
        $user_type = ($type == 'graduate') ? 'graduate' : 'company';
        
        // Modify query to include IC number check for graduates
        if ($type == 'graduate') {
            $sql = "SELECT id, name FROM $table WHERE email = ? AND ic_number = ? AND user_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $email, $ic_number, $user_type);
        } else {
            $sql = "SELECT id, name FROM $table WHERE email = ? AND user_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $user_type);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate OTP (6-digit number)
            $otp = sprintf("%06d", mt_rand(1, 999999));
            
            // Set expiry time (30 minutes from now)
            $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            
            // Update user record with OTP and expiry
            $sql = "UPDATE $table SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $otp, $expiry, $user['id']);
            
            if ($stmt->execute()) {
                // Store email and type in session for verification
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_type'] = $type;
                
                // Prepare email content
                $subject = "Password Reset OTP - Politeknik Brunei Marketing Day";
                $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4285f4; color: white; padding: 10px 20px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                        .otp { font-size: 24px; font-weight: bold; text-align: center; margin: 20px 0; letter-spacing: 5px; color: #4285f4; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Password Reset Request</h2>
                        </div>
                        <div class='content'>
                            <p>Dear " . htmlspecialchars($user['name']) . ",</p>
                            <p>We received a request to reset your password for your Politeknik Brunei Marketing Day account. Please use the following One-Time Password (OTP) to complete your password reset:</p>
                            <div class='otp'>" . $otp . "</div>
                            <p>This OTP will expire in 30 minutes.</p>
                            <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated email. Please do not reply to this message.</p>
                            <p>&copy; " . date('Y') . " Politeknik Brunei Marketing Day. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
                ";
                
                // Send email
                if (sendEmail($email, $subject, $body)) {
                    // Redirect to OTP verification page
                    header("Location: /Website/authentication/verify_otp.php");
                    exit();
                } else {
                    $message = "Failed to send OTP email. Please try again later.";
                    $messageType = "error";
                }
            } else {
                $message = "Failed to process your request. Please try again.";
                $messageType = "error";
            }
        } else {
            $message = "Email not found. Please check your email address and try again.";
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
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <?php if ($type == 'graduate'): ?>
            <div class="form-group">
                <label for="ic_number">IC Number:</label>
                <input type="text" id="ic_number" name="ic_number" required>
            </div>
            <?php endif; ?>
            
            <button type="submit">Send Reset OTP</button>
        </form>
        
        <div class="back-link">
            <a href="/Website/index.php">Back to Login</a>
        </div>
    </div>
</body>
</html>