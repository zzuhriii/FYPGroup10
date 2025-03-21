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
    
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get job details
    $job_sql = "SELECT * FROM jobs WHERE job_ID = '$job_id'";
    $job_result = mysqli_query($conn, $job_sql);
    
    if (mysqli_num_rows($job_result) == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $job = mysqli_fetch_assoc($job_result);
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $vacancy = mysqli_real_escape_string($conn, $_POST['vacancy']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Update job in database
        $update_sql = "UPDATE jobs SET 
                      job_Title = '$title', 
                      job_Category = '$category', 
                      job_Vacancy = '$vacancy', 
                      job_Description = '$description' 
                      WHERE job_ID = '$job_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            $message = "Job updated successfully!";
            $messageType = "success";
            
            // Refresh job data
            $job_result = mysqli_query($conn, $job_sql);
            $job = mysqli_fetch_assoc($job_result);
        } else {
            $message = "Error updating job: " . mysqli_error($conn);
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
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-container {
            background-color: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 16px;
        }
        
        textarea {
            height: 200px;
            resize: vertical;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .submit-btn {
            background-color: #4285f4;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .submit-btn:hover {
            background-color: #3367d6;
        }
        
        .cancel-btn {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .cancel-btn:hover {
            background-color: #e9e9e9;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
    </style>
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