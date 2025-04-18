<?php
// Remove the session_start since it's already in header.php
// session_start();

// Include header
include 'header.php';

// Check if user is logged in as a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    // Add debugging to see what's happening
    echo "<!-- Debug: user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . " -->";
    echo "<!-- Debug: user_type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'not set') . " -->";
    
    header("Location: /Website/index.php");
    exit();
}

$company_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Fix the column name from employer_ID to company_id (or whatever your column is actually named)
// You may need to check your database structure to confirm the correct column name
$jobs_sql = "SELECT job_ID, job_Title FROM jobs WHERE company_id = '$company_id'";
$jobs_result = mysqli_query($conn, $jobs_sql);

// Handle calling next applicant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['call_next'])) {
    $job_id = mysqli_real_escape_string($conn, $_POST['job_id']);
    
    // Check if we're calling a specific applicant
    if (isset($_POST['specific_applicant_id'])) {
        $application_id = mysqli_real_escape_string($conn, $_POST['specific_applicant_id']);
        
        // Get the specific applicant details
        $applicant_sql = "SELECT ja.id, ja.user_id, ja.queue_position, u.email, u.name, u.phone 
                    FROM job_applications ja 
                    JOIN users u ON ja.user_id = u.id 
                    WHERE ja.id = '$application_id' AND ja.status = 'pending'";
        $applicant_result = mysqli_query($conn, $applicant_sql);
        
        if (mysqli_num_rows($applicant_result) > 0) {
            $next_applicant = mysqli_fetch_assoc($applicant_result);
        } else {
            $message = "Applicant not found or already called.";
            $messageType = "error";
        }
    } else {
        // Get the next applicant in queue (lowest queue position with status 'pending')
        $next_sql = "SELECT ja.id, ja.user_id, ja.queue_position, u.email, u.name, u.phone 
                    FROM job_applications ja 
                    JOIN users u ON ja.user_id = u.id 
                    WHERE ja.job_id = '$job_id' AND ja.status = 'pending' 
                    ORDER BY ja.queue_position ASC LIMIT 1";
        $next_result = mysqli_query($conn, $next_sql);
        
        if (mysqli_num_rows($next_result) > 0) {
            $next_applicant = mysqli_fetch_assoc($next_result);
        } else {
            $message = "No pending applicants in queue for this job.";
            $messageType = "info";
        }
    }
    
    if (isset($next_applicant)) {
        $application_id = $next_applicant['id'];
        $applicant_email = $next_applicant['email'];
        $applicant_name = $next_applicant['name'];
        $applicant_phone = $next_applicant['phone'];
        $queue_position = $next_applicant['queue_position'];
        
        // Update status to 'viewed' (being called)
        $update_sql = "UPDATE job_applications SET status = 'viewed', email_sent = 0 WHERE id = '$application_id'";
        if (mysqli_query($conn, $update_sql)) {
            // Get job details
            $job_sql = "SELECT job_Title FROM jobs WHERE job_ID = '$job_id'";
            $job_result = mysqli_query($conn, $job_sql);
            $job_title = mysqli_fetch_assoc($job_result)['job_Title'];
            
            // Remove the direct mail() function and use the mailer.php instead
            require_once '../includes/mailer.php';
            if (sendInterviewNotification($applicant_email, $applicant_name, $job_title, $queue_position)) {
                $message = "Successfully called applicant #$queue_position ($applicant_name). An email notification has been sent.";
                $messageType = "success";
            } else {
                $message = "Called applicant #$queue_position ($applicant_name), but there was an issue sending the email notification.";
                $messageType = "warning";
            }
        } else {
            $message = "Error updating application status: " . mysqli_error($conn);
            $messageType = "error";
        }
    }
}

// Handle marking applicant as interviewed/accepted/declined
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $application_id = mysqli_real_escape_string($conn, $_POST['application_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $decline_reason = isset($_POST['decline_reason']) ? mysqli_real_escape_string($conn, $_POST['decline_reason']) : '';
    
    $update_sql = "UPDATE job_applications SET status = '$new_status'";
    if ($new_status === 'declined' && !empty($decline_reason)) {
        $update_sql .= ", decline_reason = '$decline_reason'";
    }
    $update_sql .= " WHERE id = '$application_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        // Get applicant details
        $applicant_sql = "SELECT ja.user_id, ja.job_id, ja.queue_position, u.email, u.name, j.job_Title 
                         FROM job_applications ja 
                         JOIN users u ON ja.user_id = u.id 
                         JOIN jobs j ON ja.job_id = j.job_ID 
                         WHERE ja.id = '$application_id'";
        $applicant_result = mysqli_query($conn, $applicant_sql);
        $applicant_data = mysqli_fetch_assoc($applicant_result);
        
        // Use the mailer.php functions instead of direct mail()
        require_once '../includes/mailer.php';
        if (sendStatusUpdateEmail($applicant_data['email'], $applicant_data['name'], $applicant_data['job_Title'], $new_status, $decline_reason, $applicant_data['queue_position'])) {
            $message = "Application status updated to '" . ucfirst($new_status) . "'. Notification email sent to applicant.";
            $messageType = "success";
        } else {
            $message = "Application status updated, but there was an issue sending the email notification.";
            $messageType = "warning";
        }
    } else {
        $message = "Error updating application status: " . mysqli_error($conn);
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Application Queue</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
        }
        
        h1:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: #4285f4;
            border-radius: 2px;
        }
        
        .message {
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
        }
        
        .message:before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .success:before {
            content: "\f00c";
            color: #28a745;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .error:before {
            content: "\f071";
            color: #dc3545;
        }
        
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .info:before {
            content: "\f05a";
            color: #17a2b8;
        }
        
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .warning:before {
            content: "\f071";
            color: #ffc107;
        }
        
        .job-selector {
            margin-bottom: 40px;
            padding: 25px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .job-selector:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .job-selector h2 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
            font-weight: 600;
            position: relative;
            padding-left: 15px;
        }
        
        .job-selector h2:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 5px;
            background-color: #4285f4;
            border-radius: 3px;
        }
        
        .job-selector select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            font-size: 16px;
            color: #495057;
            background-color: #f8f9fa;
            transition: border-color 0.3s, box-shadow 0.3s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234285f4' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }
        
        .job-selector select:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.25);
            outline: none;
        }
        
        .call-next-btn {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: block;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 4px 6px rgba(66, 133, 244, 0.2);
        }
        
        .call-next-btn:hover {
            background-color: #3367d6;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(66, 133, 244, 0.3);
        }
        
        .call-next-btn:active {
            transform: translateY(0);
        }
        
        .queue-list {
            margin-top: 40px;
        }
        
        .queue-list h2 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            padding-left: 15px;
        }
        
        .queue-list h2:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 5px;
            background-color: #4285f4;
            border-radius: 3px;
        }
        
        .queue-list h3 {
            color: #4285f4;
            font-size: 20px;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e5eb;
        }
        
        .queue-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 15px;
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .queue-table th, .queue-table td {
            padding: 15px;
            text-align: left;
        }
        
        .queue-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
            border-bottom: 2px solid #e1e5eb;
        }
        
        .queue-table tr:not(:last-child) td {
            border-bottom: 1px solid #e1e5eb;
        }
        
        .queue-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-right: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn:before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 6px;
        }
        
        .interview-btn {
            background-color: #17a2b8;
            color: white;
        }
        
        .interview-btn:before {
            content: "\f4ad";
        }
        
        .interview-btn:hover {
            background-color: #138496;
            transform: translateY(-2px);
        }
        
        .accept-btn {
            background-color: #28a745;
            color: white;
        }
        
        .accept-btn:before {
            content: "\f00c";
        }
        
        .accept-btn:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        
        .decline-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .decline-btn:before {
            content: "\f00d";
        }
        
        .decline-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .call-btn {
            background-color: #6f42c1;
            color: white;
        }
        
        .call-btn:before {
            content: "\f095";
        }
        
        .call-btn:hover {
            background-color: #5a32a3;
            transform: translateY(-2px);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 12px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e1e5eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .close-modal {
            font-size: 28px;
            font-weight: bold;
            color: #adb5bd;
            cursor: pointer;
            background: none;
            border: none;
            transition: color 0.2s;
        }
        
        .close-modal:hover {
            color: #495057;
        }
        
        .modal-body {
            margin-bottom: 25px;
        }
        
        .modal-body p {
            margin-bottom: 15px;
            color: #495057;
        }
        
        .modal-footer {
            text-align: right;
        }
        
        .modal-footer button {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            margin-left: 12px;
            transition: all 0.2s;
        }
        
        .cancel-btn {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #ced4da;
        }
        
        .cancel-btn:hover {
            background-color: #e9ecef;
        }
        
        .confirm-btn {
            background-color: #4285f4;
            color: white;
        }
        
        .confirm-btn:hover {
            background-color: #3367d6;
            transform: translateY(-2px);
        }
        
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            font-size: 15px;
            color: #495057;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        textarea:focus {
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.25);
            outline: none;
        }
        
        /* Floating back button styles */
        .floating-back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 14px 25px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .floating-back-btn:hover {
            background-color: #3367d6;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        
        .floating-back-btn:before {
            content: "\f060";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
        }
        
        /* Empty state styling */
                .empty-state {
                    text-align: center;
                    padding: 40px 20px;
                    background-color: white;
                    border-radius: 12px;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
                }
                
                .empty-state i {
                    font-size: 60px;
                    color: #adb5bd;
                    margin-bottom: 20px;
                }
                
                .empty-state p {
                    color: #6c757d;
                    font-size: 16px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Manage Application Queue</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="job-selector">
                    <h2>Select Job and Call Next Applicant</h2>
                    <form method="POST" action="">
                        <select name="job_id" id="job_id" required>
                            <option value="">-- Select Job --</option>
                            <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                                <option value="<?php echo $job['job_ID']; ?>"><?php echo htmlspecialchars($job['job_Title']); ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" name="call_next" class="call-next-btn">Call Next Applicant</button>
                    </form>
                </div>
                
                <div class="queue-list">
                    <h2>Current Application Queue</h2>
                    
                    <?php
                    // Reset the result pointer to the beginning
                    mysqli_data_seek($jobs_result, 0);
                    
                    // Loop through each job to display its queue
                    while ($job = mysqli_fetch_assoc($jobs_result)):
                        $job_id = $job['job_ID'];
                        $job_title = $job['job_Title'];
                        
                        // Get all applications for this job
                        $applications_sql = "SELECT ja.id, ja.user_id, ja.status, ja.queue_position, ja.application_date, 
                                        u.name, u.email, u.phone
                                        FROM job_applications ja 
                                        JOIN users u ON ja.user_id = u.id 
                                        WHERE ja.job_id = '$job_id' 
                                        ORDER BY ja.queue_position ASC";
                        $applications_result = mysqli_query($conn, $applications_sql);
                        
                        if (mysqli_num_rows($applications_result) > 0):
                    ?>
                        <h3><?php echo htmlspecialchars($job_title); ?></h3>
                        <table class="queue-table">
                            <thead>
                                <tr>
                                    <th>Queue #</th>
                                    <th>Applicant</th>
                                    <th>Contact</th>
                                    <th>Applied On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($application = mysqli_fetch_assoc($applications_result)): ?>
                                    <tr>
                                        <td><?php echo $application['queue_position']; ?></td>
                                        <td><?php echo htmlspecialchars($application['name']); ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($application['email']); ?></div>
                                            <div><?php echo htmlspecialchars($application['phone']); ?></div>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($application['application_date'])); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $application['status']; ?>">
                                                <?php echo ucfirst($application['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($application['status'] === 'pending'): ?>
                                                <button class="action-btn call-btn" onclick="callApplicant(<?php echo $application['id']; ?>, <?php echo $job_id; ?>)">
                                                    Call Now
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($application['status'] === 'viewed'): ?>
                                                <button class="action-btn interview-btn" onclick="updateStatus(<?php echo $application['id']; ?>, 'interviewed')">
                                                    Interviewed
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($application['status'] === 'interviewed'): ?>
                                                <button class="action-btn accept-btn" onclick="updateStatus(<?php echo $application['id']; ?>, 'accepted')">
                                                    Accept
                                                </button>
                                                <button class="action-btn decline-btn" onclick="showDeclineModal(<?php echo $application['id']; ?>)">
                                                    Decline
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <h3><?php echo htmlspecialchars($job_title); ?></h3>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No applications for this job yet.</p>
                        </div>
                    <?php endif; endwhile; ?>
                </div>
            </div>
            
            <!-- Modal for decline reason -->
            <div id="declineModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Provide Feedback (Optional)</h3>
                        <button class="close-modal" onclick="closeModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Please provide feedback for the applicant (optional):</p>
                        <form id="declineForm" method="POST" action="">
                            <input type="hidden" id="application_id" name="application_id" value="">
                            <input type="hidden" name="new_status" value="declined">
                            <textarea name="decline_reason" placeholder="Explain why the applicant was not selected..."></textarea>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="cancel-btn" onclick="closeModal()">Cancel</button>
                        <button class="confirm-btn" onclick="submitDecline()">Submit & Decline</button>
                    </div>
                </div>
            </div>
            
            <!-- Floating Back to Dashboard Button -->
            <a href="/Website/company_profile/company_dashboard.php" class="floating-back-btn">
                Back to Dashboard
            </a>
            
            <script>
                function updateStatus(applicationId, newStatus) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    
                    const applicationIdInput = document.createElement('input');
                    applicationIdInput.type = 'hidden';
                    applicationIdInput.name = 'application_id';
                    applicationIdInput.value = applicationId;
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'new_status';
                    statusInput.value = newStatus;
                    
                    const submitInput = document.createElement('input');
                    submitInput.type = 'hidden';
                    submitInput.name = 'update_status';
                    submitInput.value = '1';
                    
                    form.appendChild(applicationIdInput);
                    form.appendChild(statusInput);
                    form.appendChild(submitInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
                
                function showDeclineModal(applicationId) {
                    document.getElementById('application_id').value = applicationId;
                    document.getElementById('declineModal').style.display = 'block';
                }
                
                function closeModal() {
                    document.getElementById('declineModal').style.display = 'none';
                }
                
                function submitDecline() {
                    const form = document.getElementById('declineForm');
                    const updateStatusInput = document.createElement('input');
                    updateStatusInput.type = 'hidden';
                    updateStatusInput.name = 'update_status';
                    updateStatusInput.value = '1';
                    
                    form.appendChild(updateStatusInput);
                    form.submit();
                }
                
                function callApplicant(applicationId, jobId) {
                    // Create a form and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '';
                    
                    const jobIdInput = document.createElement('input');
                    jobIdInput.type = 'hidden';
                    jobIdInput.name = 'job_id';
                    jobIdInput.value = jobId;
                    
                    const callNextInput = document.createElement('input');
                    callNextInput.type = 'hidden';
                    callNextInput.name = 'call_next';
                    callNextInput.value = '1';
                    
                    const specificApplicantInput = document.createElement('input');
                    specificApplicantInput.type = 'hidden';
                    specificApplicantInput.name = 'specific_applicant_id';
                    specificApplicantInput.value = applicationId;
                    
                    form.appendChild(jobIdInput);
                    form.appendChild(callNextInput);
                    form.appendChild(specificApplicantInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            </script>
        </body>
        </html>