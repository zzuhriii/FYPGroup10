<?php
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Handle job deletion
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $job_id = $_GET['delete'];
        
        // Use prepared statement for deletion
        $delete_sql = "DELETE FROM jobs WHERE job_ID = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $job_id);
        
        if ($stmt->execute()) {
            $message = "Job deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting job: " . $conn->error;
            $messageType = "error";
        }
    }
    
    // Handle job status toggle (open/close)
    if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
        $job_id = $_GET['toggle_status'];
        
        // First, get the current status
        $status_check_sql = "SELECT is_active FROM jobs WHERE job_ID = ? AND company_id = ?";
        $check_stmt = $conn->prepare($status_check_sql);
        $check_stmt->bind_param("ii", $job_id, $user_id);
        $check_stmt->execute();
        $status_result = $check_stmt->get_result();
        
        if ($status_result && $status_result->num_rows > 0) {
            $job_data = $status_result->fetch_assoc();
            $new_status = $job_data['is_active'] ? 0 : 1; // Toggle the status
            
            // If trying to close the job (changing from active to inactive)
            if ($job_data['is_active'] == 1) {
                // Check for pending applications
                $pending_check_sql = "SELECT COUNT(*) as pending_count FROM job_applications 
                                     WHERE job_id = ? AND status = 'pending'";
                $pending_stmt = $conn->prepare($pending_check_sql);
                $pending_stmt->bind_param("i", $job_id);
                $pending_stmt->execute();
                $pending_result = $pending_stmt->get_result();
                $pending_data = $pending_result->fetch_assoc();
                
                if ($pending_data['pending_count'] > 0) {
                    $message = "Warning: There are still " . $pending_data['pending_count'] . " pending application(s) for this job. Please review all applications before closing.";
                    $messageType = "warning";
                    // Don't update the status yet
                    $new_status = 1; // Keep it active
                } else {
                    // Update the status
                    $update_sql = "UPDATE jobs SET is_active = ? WHERE job_ID = ? AND company_id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("iii", $new_status, $job_id, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $status_text = $new_status ? "opened" : "closed";
                        $message = "Job application successfully $status_text!";
                        $messageType = "success";
                    } else {
                        $message = "Error updating job status: " . $conn->error;
                        $messageType = "error";
                    }
                }
            } else {
                // If opening the job, just update the status
                $update_sql = "UPDATE jobs SET is_active = ? WHERE job_ID = ? AND company_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("iii", $new_status, $job_id, $user_id);
                
                if ($update_stmt->execute()) {
                    $status_text = $new_status ? "opened" : "closed";
                    $message = "Job application successfully $status_text!";
                    $messageType = "success";
                } else {
                    $message = "Error updating job status: " . $conn->error;
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
    <title>Manage Jobs - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/manage_jobs.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Manage Your Jobs</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="/Website/company_profile/company_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="post_job.php" class="post-job-btn">Post a New Job</a>
        </div>
        
        <?php
            // Get all jobs for this company
            $sql = "SELECT * FROM jobs WHERE company_id = ? ORDER BY job_Offered DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                echo "<table class='jobs-table'>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Category</th>
                            <th>Vacancies</th>
                            <th>Salary</th>
                            <th>Posted On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                while ($row = $result->fetch_assoc()) {
                    // Format date properly with error handling
                    $date_display = "N/A";
                    if (!empty($row['job_Offered']) && $row['job_Offered'] != '0000-00-00' && $row['job_Offered'] != '0000-00-00 00:00:00') {
                        $timestamp = strtotime($row['job_Offered']);
                        if ($timestamp && $timestamp > 0) {
                            $date_display = date('M d, Y h:i A', $timestamp);
                        } else {
                            // If timestamp conversion fails, try to display the raw date
                            $date_display = $row['job_Offered'];
                        }
                    } else if (!empty($row['job_Created'])) {
                        // Fallback to job_Created if job_Offered is empty
                        $timestamp = strtotime($row['job_Created']);
                        if ($timestamp && $timestamp > 0) {
                            $date_display = date('M d, Y h:i A', $timestamp) . ' (created)';
                        }
                    }
                    
                    // Format job category to match post_job.php categories
                    $category = $row['job_Category'];
                    switch($category) {
                        case 'full_time':
                            $category = 'Full Time';
                            break;
                        case 'part_time':
                            $category = 'Part Time';
                            break;
                        case 'internship':
                            $category = 'Internship';
                            break;
                        case 'contract':
                            $category = 'Contract';
                            break;
                        case 'temporary':
                            $category = 'Temporary';
                            break;
                        default:
                            $category = $row['job_Category'];
                    }
                    
                    // Determine job status and button text
                    $is_active = isset($row['is_active']) ? $row['is_active'] : 1; // Default to active if column doesn't exist
                    $status_class = $is_active ? 'status-open' : 'status-closed';
                    $status_text = $is_active ? 'Open' : 'Closed';
                    $toggle_text = $is_active ? 'Close' : 'Open';
                    
                    echo "<tr>
                        <td>" . htmlspecialchars($row['job_Title']) . "</td>
                        <td>" . htmlspecialchars($category) . "</td>
                        <td>" . htmlspecialchars($row['job_Vacancy']) . "</td>
                        <td>".(($row['min_salary'] && $row['max_salary']) ? "BND" . number_format($row['min_salary']) . " - BND" . number_format($row['max_salary']) . " per month" : 'Not specified')."</td>
                        <td>" . $date_display . "</td>
                        <td><span class='" . $status_class . "'>" . $status_text . "</span></td>
                        <td class='action-links'>
                            <a href='edit_job.php?id=" . $row['job_ID'] . "' class='edit-link'>Edit</a>
                            <a href='manage_jobs.php?delete=" . $row['job_ID'] . "' class='delete-link' onclick='return confirm(\"Are you sure you want to delete this job?\")'>Delete</a>
                            <a href='view_applications.php?job_id=" . $row['job_ID'] . "' class='view-link'>View Applications</a>
                            <a href='manage_jobs.php?toggle_status=" . $row['job_ID'] . "' class='toggle-link' onclick='return confirm(\"Are you sure you want to " . strtolower($toggle_text) . " applications for this job?\")'>". $toggle_text ."</a>
                        </td>
                    </tr>";
                }
                
                echo "</tbody>
                </table>";
            } else {
                echo "<div class='no-jobs'>
                    <p>You haven't posted any jobs yet.</p>
                    <p>Click the 'Post a New Job' button to create your first job listing.</p>
                </div>";
            }
        ?>
    </div>

<style>
    .status-open {
        color: green;
        font-weight: bold;
    }
    .status-closed {
        color: red;
        font-weight: bold;
    }
    .toggle-link {
        background-color: #6c757d;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        text-decoration: none;
    }
    .toggle-link:hover {
        background-color: #5a6268;
    }
    .message.warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
</style>
</body>
</html>
