<?php
// Start the session
session_start();

// Include database connection
require_once '../includes/db.php';

// Check if user is coming from forgot password page
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_type'])) {
    header("Location: /Website/index.php");
    exit();
}

$email = $_SESSION['reset_email'];
$type = $_SESSION['reset_type'];
$table = ($type == 'graduate') ? 'users' : 'companies';

$message = '';
$messageType = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($otp) || empty($new_password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "error";
    } else {
        // Verify OTP
        $sql = "SELECT id FROM $table WHERE email = ? AND reset_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Check if token is expired
            $user = $result->fetch_assoc();
            $sql_expiry = "SELECT reset_token_expiry FROM $table WHERE id = ?";
            $stmt_expiry = $conn->prepare($sql_expiry);
            $stmt_expiry->bind_param("i", $user['id']);
            $stmt_expiry->execute();
            $result_expiry = $stmt_expiry->get_result();
            $expiry_data = $result_expiry->fetch_assoc();
            
            if (strtotime($expiry_data['reset_token_expiry']) > time()) {
                // Token is valid and not expired
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $sql = "UPDATE $table SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $hashed_password, $user['id']);
                
                if ($stmt->execute()) {
                    $message = "Password has been reset successfully. You can now login with your new password.";
                    $messageType = "success";
                    
                    // Clear session variables
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_type']);
                    
                    // Redirect to login page after 3 seconds
                    header("refresh:3;url=/Website/index.php");
                } else {
                    $message = "Failed to reset password. Please try again.";
                    $messageType = "error";
                }
            } else {
                $message = "OTP has expired. Please request a new one.";
                $messageType = "error";
            }
        } else {
            // For debugging purposes
            $sql_check = "SELECT reset_token, reset_token_expiry FROM $table WHERE email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            $user_check = $result_check->fetch_assoc();
            
            if ($user_check && $user_check['reset_token']) {
                if ($user_check['reset_token'] != $otp) {
                    $message = "Invalid OTP.";
                    $messageType = "error";
                } else {
                    $message = "Invalid OTP or email combination.";
                    $messageType = "error";
                }
            } else {
                $message = "No reset token found for this email.";
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/verify_otp.css">

</head>
<body>
    <div class="container">
        <h2>Verify OTP & Reset Password</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="otp">Enter OTP sent to your email:</label>
                <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 8 characters)" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            </div>
            
            <button type="submit">Reset Password</button>
        </form>
        
        <div class="back-link">
            <a href="/Website/authentication/forgot_password.php?type=<?php echo $type; ?>">Back to Forgot Password</a>
        </div>
    </div>
</body>
</html>