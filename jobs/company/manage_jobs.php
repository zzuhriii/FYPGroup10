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
                            $date_display = date('M d, Y', $timestamp);
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
                    
                    echo "<tr>
                        <td>" . htmlspecialchars($row['job_Title']) . "</td>
                        <td>" . htmlspecialchars($category) . "</td>
                        <td>" . htmlspecialchars($row['job_Vacancy']) . "</td>
                        <td>" . ($row['salary_estimation'] ? htmlspecialchars($row['salary_estimation']) . ' per month' : 'Not specified') . "</td>
                        <td>" . $date_display . "</td>
                        <td class='action-links'>
                            <a href='edit_job.php?id=" . $row['job_ID'] . "' class='edit-link'>Edit</a>
                            <a href='manage_jobs.php?delete=" . $row['job_ID'] . "' class='delete-link' onclick='return confirm(\"Are you sure you want to delete this job?\")'>Delete</a>
                            <a href='view_applications.php?job_id=" . $row['job_ID'] . "' class='view-link'>View Applications</a>
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
</body>
</html>