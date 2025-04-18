<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if application ID and action are provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['action'])) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $application_id = $_GET['id'];
    $action = $_GET['action'];
    
    // Validate action
    if ($action != 'accept' && $action != 'decline') {
        header("Location: manage_jobs.php");
        exit();
    }
    
    // Get application details to find the job_id
    $app_sql = "SELECT job_id, user_id FROM job_applications WHERE id = ?";
    $stmt = $conn->prepare($app_sql);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $app_result = $stmt->get_result();
    
    if ($app_result->num_rows == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $application = $app_result->fetch_assoc();
    $job_id = $application['job_id'];
    $applicant_id = $application['user_id'];
    
    // If action is decline and no POST data yet, show the form to enter decline reason
    if ($action == 'decline' && !isset($_POST['submit'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Decline Application - Politeknik Brunei</title>
            <link rel="stylesheet" href="/Website/assets/css/index.css">
            <link rel="stylesheet" href="/Website/assets/css/respond_application.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        </head>
        <body>
            <!-- Politeknik Logo at top left -->
            <div style="position: absolute; top: 10px; left: 10px;">
                <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
            </div>
            
            <div class="decline-form">
                <h2 class="page-title">Decline Application</h2>
                <p class="instructions">Please provide a reason for declining this application:</p>
                
                <form method="POST" action="respond_application.php?id=<?php echo $application_id; ?>&action=decline">
                    <div class="form-group">
                        <label for="decline_reason">Reason for declining:</label>
                        <textarea id="decline_reason" name="decline_reason" required></textarea>
                    </div>
                    <div class="btn-group">
                        <a href="view_applications.php?job_id=<?php echo $job_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Update application status
    $status = ($action == 'accept') ? 'accepted' : 'declined';
    
    if ($action == 'decline' && isset($_POST['submit'])) {
        // Get the decline reason
        $decline_reason = $_POST['decline_reason'];
        
        // Update with decline reason
        $update_sql = "UPDATE job_applications SET status = ?, decline_reason = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $status, $decline_reason, $application_id);
    } else {
        // Regular update for acceptance
        $update_sql = "UPDATE job_applications SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $status, $application_id);
    }
    
    if ($update_stmt->execute()) {
        // Send email notification to the applicant
        $applicant_sql = "SELECT u.email, u.name, j.job_Title 
                          FROM users u 
                          JOIN job_applications ja ON u.id = ja.user_id 
                          JOIN jobs j ON ja.job_id = j.job_ID 
                          WHERE ja.id = ?";
        $applicant_stmt = $conn->prepare($applicant_sql);
        $applicant_stmt->bind_param("i", $application_id);
        $applicant_stmt->execute();
        $applicant_result = $applicant_stmt->get_result();
        
        if ($applicant_result->num_rows > 0) {
            $applicant_data = $applicant_result->fetch_assoc();
            $to = $applicant_data['email'];
            $subject = "Update on Your Job Application - " . $applicant_data['job_Title'];
            
            if ($status == 'accepted') {
                $message = "Dear " . $applicant_data['name'] . ",\n\n";
                $message .= "Congratulations! Your application for the position of " . $applicant_data['job_Title'] . " has been accepted.\n\n";
                $message .= "Please check your email for further instructions regarding the next steps in the hiring process.\n\n";
                $message .= "Best regards,\nThe Recruitment Team";
            } else {
                $message = "Dear " . $applicant_data['name'] . ",\n\n";
                $message .= "Thank you for your interest in the position of " . $applicant_data['job_Title'] . ".\n\n";
                $message .= "After careful consideration, we regret to inform you that we have decided not to proceed with your application at this time.\n\n";
                $message .= "Reason: " . $decline_reason . "\n\n";
                $message .= "We appreciate your interest in our company and wish you success in your job search.\n\n";
                $message .= "Best regards,\nThe Recruitment Team";
            }
            
            $headers = "From: noreply@politeknikbrunei.edu.bn";
            
            // Uncomment this line to actually send emails in production
            // mail($to, $subject, $message, $headers);
        }
        
        $message = "Application has been " . $status . " successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating application: " . $conn->error;
        $messageType = "error";
    }
    
    // Redirect back to the view applications page
    header("Location: view_applications.php?job_id=" . $job_id . "&message=" . urlencode($message) . "&messageType=" . $messageType);
    exit();
?>