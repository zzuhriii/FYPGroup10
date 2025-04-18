<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if job ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job_id = $_GET['id'];
    
    // Get job details
    $job_sql = "SELECT * FROM jobs WHERE job_ID = ?";
    $stmt = $conn->prepare($job_sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job_result = $stmt->get_result();
    
    if ($job_result->num_rows == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job = $job_result->fetch_assoc();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $title = $_POST['title'];
        $category = $_POST['category'];
        $vacancy = $_POST['vacancy'];
        $description = $_POST['description'];
        
        // Update job in database
        $update_sql = "UPDATE jobs SET 
                      job_Title = ?, 
                      job_Category = ?, 
                      job_Vacancy = ?, 
                      job_Description = ? 
                      WHERE job_ID = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssisi", $title, $category, $vacancy, $description, $job_id);
        
        if ($update_stmt->execute()) {
            $message = "Job updated successfully!";
            $messageType = "success";
            
            // Refresh job data
            $stmt->execute();
            $job_result = $stmt->get_result();
            $job = $job_result->fetch_assoc();
        } else {
            $message = "Error updating job: " . $conn->error;
            $messageType = "error";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/edit_job.css">
</head>
<body>
    <div class="container">
        <h1>Edit Job</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($job['job_Title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select a category</option>
                        <option value="full_time" <?php if ($job['job_Category'] == 'full_time') echo 'selected'; ?>>Full Time</option>
                        <option value="part_time" <?php if ($job['job_Category'] == 'part_time') echo 'selected'; ?>>Part Time</option>
                        <option value="internship" <?php if ($job['job_Category'] == 'internship') echo 'selected'; ?>>Internship</option>
                        <option value="contract" <?php if ($job['job_Category'] == 'contract') echo 'selected'; ?>>Contract</option>
                        <option value="temporary" <?php if ($job['job_Category'] == 'temporary') echo 'selected'; ?>>Temporary</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="vacancy">Number of Vacancies</label>
                    <input type="number" id="vacancy" name="vacancy" min="1" value="<?php echo htmlspecialchars($job['job_Vacancy']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($job['job_Description']); ?></textarea>
                </div>
                
                <div class="button-group">
                    <a href="manage_jobs.php" class="cancel-btn">Cancel</a>
                    <button type="submit" class="submit-btn">Update Job</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>