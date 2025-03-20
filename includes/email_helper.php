<?php
// Email helper functions

/**
 * Send an email using PHP's mail function
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message_body Email body (HTML)
 * @return bool|string True on success, error message on failure
 */
function sendEmail($to, $subject, $message_body) {
    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@politeknikbrunei.edu.bn" . "\r\n";
    
    // Send email
    if (mail($to, $subject, $message_body, $headers)) {
        return true;
    } else {
        return "Failed to send email. Please check your mail server configuration.";
    }
}