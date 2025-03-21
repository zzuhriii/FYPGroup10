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
        $job_id = mysqli_real_escape_string($conn, $_GET['delete']);
        
        // For now, just delete the job without checking ownership
        // since we don't have a user relationship column yet
        $delete_sql = "DELETE FROM jobs WHERE job_ID = '$job_id'";
        if (mysqli_query($conn, $delete_sql)) {
            $message = "Job deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting job: " . mysqli_error($conn);
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
    <style>
        .container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #092D74;
            font-weight: 700;
            position: relative;
            padding-bottom: 1rem;
        }
        
        h1:after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background-color: #FFCC00;
        }
        
        .action-buttons {
            margin-bottom: 2rem;
            text-align: right;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .back-btn {
            background-color: #f8f9fa;
            color: #092D74;
            border: 2px solid #092D74;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .back-btn:hover {
            background-color: #092D74;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(9, 45, 116, 0.2);
        }
        
        .post-job-btn {
            background-color: #092D74;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(9, 45, 116, 0.2);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .post-job-btn:hover {
            background-color: #E30613;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(227, 6, 19, 0.3);
        }
        
        .jobs-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .jobs-table th, .jobs-table td {
            padding: 1rem 1.2rem;
            text-align: left;
        }
        
        .jobs-table th {
            background-color: #092D74;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .jobs-table tr {
            border-bottom: 1px solid #eee;
            transition: all 0.2s ease;
        }
        
        .jobs-table tr:last-child {
            border-bottom: none;
        }
        
        .jobs-table tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .jobs-table td {
            color: #333;
            border-bottom: 1px solid #eee;
        }
        
        .jobs-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .action-links {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-links a {
            text-decoration: none;
            display: inline-block;
            padding: 0.5rem 0.8rem;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }
        
        .edit-link {
            color: #fff;
            background-color: #4285f4;
        }
        
        .edit-link:hover {
            background-color: #2b6ed9;
            transform: translateY(-2px);
        }
        
        .delete-link {
            color: #fff;
            background-color: #E30613;
        }
        
        .delete-link:hover {
            background-color: #c00511;
            transform: translateY(-2px);
        }
        
        .view-link {
            color: #fff;
            background-color: #28a745;
        }
        
        .view-link:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        
        .message {
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .no-jobs {
            text-align: center;
            padding: 3rem 2rem;
            background-color: #f9f9f9;
            color: #555;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            border-left: 4px solid #FFCC00;
        }
        
        .no-jobs p {
            margin: 0.8rem 0;
            font-size: 1.1rem;
        }
        
        .no-jobs p:first-child {
            font-weight: 600;
            color: #092D74;
            font-size: 1.3rem;
        }
    </style>
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
            // Get all jobs (since we don't have a user relationship yet)
            $sql = "SELECT * FROM jobs ORDER BY job_Offered DESC";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
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
                
                while ($row = mysqli_fetch_assoc($result)) {
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
                        <td>".$row['job_Title']."</td>
                        <td>".$category."</td>
                        <td>".$row['job_Vacancy']."</td>
                        <td>".($row['salary_estimation'] ? htmlspecialchars($row['salary_estimation']) . ' per month' : 'Not specified')."</td>
                        <td>".$date_display."</td>
                        <td class='action-links'>
                            <a href='edit_job.php?id=".$row['job_ID']."' class='edit-link'>Edit</a>
                            <a href='manage_jobs.php?delete=".$row['job_ID']."' class='delete-link' onclick='return confirm(\"Are you sure you want to delete this job?\")'>Delete</a>
                            <a href='view_applications.php?job_id=".$row['job_ID']."' class='view-link'>View Applications</a>
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