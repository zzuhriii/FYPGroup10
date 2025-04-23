<?php
// Remove the session_start since it's already in header.php
// session_start();

// Include header
include 'header.php';
    
// Include the floating button component
include_once '../includes/floating_button.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/css/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gray-dark: #343a40;
            --border-radius: 10px;
            --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 30px;
            text-align: center;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .message {
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .message:before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 15px;
            font-size: 20px;
        }
        
        .success {
            background-color: rgba(76, 201, 240, 0.15);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .success:before {
            content: "\f00c";
            color: var(--success);
        }
        
        .error {
            background-color: rgba(247, 37, 133, 0.15);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .error:before {
            content: "\f071";
            color: var(--danger);
        }
        
        .info {
            background-color: rgba(72, 149, 239, 0.15);
            color: var(--info);
            border-left: 4px solid var(--info);
        }
        
        .info:before {
            content: "\f05a";
            color: var(--info);
        }
        
        .warning {
            background-color: rgba(248, 150, 30, 0.15);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }
        
        .warning:before {
            content: "\f071";
            color: var(--warning);
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .card-header i {
            font-size: 24px;
            color: var(--primary);
            margin-right: 15px;
            background-color: rgba(67, 97, 238, 0.1);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .card-header h2 {
            margin: 0;
            color: var(--dark);
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        /* Job selector styles */
        .job-selector select {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 16px;
            font-family: inherit;
            margin-bottom: 20px;
            background-color: var(--light);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%234361ee' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            transition: all 0.3s;
        }
        
        .job-selector select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.25);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        
        .btn-info {
            background-color: var(--info);
            color: white;
        }
        
        .btn-warning {
            background-color: var(--warning);
            color: white;
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }
        
        /* Queue table styles */
        .queue-section {
            margin-top: 40px;
        }
        
        .section-title {
            font-size: 1.75rem;
            color: var(--primary);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section-title:before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 30px;
            background-color: var(--primary);
            border-radius: 4px;
            margin-right: 15px;
        }
        
        .job-title {
            background-color: var(--light);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin: 30px 0 15px;
            color: var(--dark);
            font-size: 1.25rem;
            font-weight: 600;
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
        }
        
        .job-title i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .queue-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: white;
            overflow: hidden;
        }
        
        .queue-table th,
        .queue-table td {
            padding: 15px;
            text-align: left;
        }
        
        .queue-table th {
            background-color: rgba(67, 97, 238, 0.05);
            font-weight: 600;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--primary);
        }
        
        .queue-table tbody tr {
            transition: all 0.2s;
        }
        
        .queue-table tbody tr:nth-child(odd) {
            background-color: rgba(67, 97, 238, 0.02);
        }
        
        .queue-table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .queue-table tr:not(:last-child) td {
            border-bottom: 1px solid var(--gray-light);
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background-color: rgba(248, 150, 30, 0.15);
            color: var(--warning);
        }
        
        .status-viewed {
            background-color: rgba(72, 149, 239, 0.15);
            color: var(--info);
        }
        
        .status-interviewed {
            background-color: rgba(72, 149, 239, 0.2);
            color: var(--secondary);
        }
        
        .status-accepted {
            background-color: rgba(76, 201, 240, 0.15);
            color: var(--success);
        }
        
        .status-declined {
            background-color: rgba(247, 37, 133, 0.15);
            color: var(--danger);
        }
        
        .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            font-family: inherit;
            margin-right: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn i {
            margin-right: 6px;
        }
        
        .interview-btn {
            background-color: var(--secondary);
            color: white;
        }
        
        .interview-btn:hover {
            background-color: #342fac;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(63, 55, 201, 0.3);
        }
        
        .accept-btn {
            background-color: var(--success);
            color: white;
        }
        
        .accept-btn:hover {
            background-color: #3db8e0;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(76, 201, 240, 0.3);
        }
        
        .decline-btn {
            background-color: var(--danger);
            color: white;
        }
        
        .decline-btn:hover {
            background-color: #e01e79;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(247, 37, 133, 0.3);
        }
        
        .call-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .call-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(67, 97, 238, 0.3);
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
            overflow-y: auto;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            animation: slideUp 0.4s;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin: 0;
        }
        
        .close-modal {
            font-size: 28px;
            font-weight: 300;
            color: var(--gray);
            cursor: pointer;
            background: none;
            border: none;
            transition: all 0.2s;
            line-height: 0.5;
            padding: 10px;
        }
        
        .close-modal:hover {
            color: var(--primary);
        }
        
        .modal-body {
            margin-bottom: 25px;
        }
        
        .modal-body p {
            margin-bottom: 15px;
            color: var(--gray-dark);
        }
        
        .modal-footer {
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        
        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
            font-size: 15px;
            color: var(--dark);
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.25);
        }
        
        /* Floating back button styles */
        .floating-back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.4);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .floating-back-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.5);
        }
        
        .floating-back-btn i {
            margin-right: 10px;
        }
        
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--gray-light);
            margin-bottom: 20px;
            display: block;
        }
        
        .empty-state p {
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        /* Animations */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        /* Responsive styles */
        @media screen and (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
        }
        
        @media screen and (max-width: 576px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-header i {
                margin-bottom: 10px;
            }
            
            .action-btn {
                padding: 6px 10px;
                font-size: 0.7rem;
                margin-bottom: 5px;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .modal-footer button {
                width: 100%;
                margin-left: 0;
                margin-bottom: 10px;
            }
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
        
        <div class="card job-selector">
            <div class="card-header">
                <i class="fas fa-bullhorn"></i>
                <h2>Select Job and Call Next Applicant</h2>
            </div>
            <form method="POST" action="">
                <select name="job_id" id="job_id" required>
                    <option value="">-- Select Job --</option>
                    <?php while ($job = mysqli_fetch_assoc($jobs_result)): ?>
                        <option value="<?php echo $job['job_ID']; ?>"><?php echo htmlspecialchars($job['job_Title']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="call_next" class="btn btn-primary btn-block">
                    <i class="fas fa-phone-alt"></i> Call Next Applicant
                </button>
            </form>
        </div>
        
        <div class="queue-section">
            <h2 class="section-title">Current Application Queue</h2>
            
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
                <h3 class="job-title">
                    <i class="fas fa-briefcase"></i>
                    <?php echo htmlspecialchars($job_title); ?>
                </h3>
                <div class="table-responsive">
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
                                        <div><i class="fas fa-envelope" style="color: var(--primary); margin-right: 5px;"></i> <?php echo htmlspecialchars($application['email']); ?></div>
                                        <div><i class="fas fa-phone" style="color: var(--primary); margin-right: 5px;"></i> <?php echo htmlspecialchars($application['phone']); ?></div>
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
                                                <i class="fas fa-phone-alt"></i> Call
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($application['status'] === 'viewed'): ?>
                                            <button class="action-btn interview-btn" onclick="updateStatus(<?php echo $application['id']; ?>, 'interviewed')">
                                                <i class="fas fa-check"></i> Interviewed
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($application['status'] === 'interviewed'): ?>
                                            <button class="action-btn accept-btn" onclick="updateStatus(<?php echo $application['id']; ?>, 'accepted')">
                                                <i class="fas fa-thumbs-up"></i> Accept
                                            </button>
                                            <button class="action-btn decline-btn" onclick="showDeclineModal(<?php echo $application['id']; ?>)">
                                                <i class="fas fa-thumbs-down"></i> Decline
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h3 class="job-title">
                    <i class="fas fa-briefcase"></i>
                    <?php echo htmlspecialchars($job_title); ?>
                </h3>
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
                <button class="btn" onclick="closeModal()">Cancel</button>
                <button class="btn btn-danger" onclick="submitDecline()">Submit & Decline</button>
            </div>
        </div>
    </div>
    
    <?php
    // Display the floating back button using our reusable component
    displayFloatingButton('Back to Dashboard', '/Website/company_profile/company_dashboard.php', 'fa-arrow-left');
    ?>
    
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