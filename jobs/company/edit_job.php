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
        $location = $_POST['location'] ?? $job['job_Location'];
        $salary = $_POST['salary'] ?? $job['salary_estimation'];
        $programme = $_POST['programme'] ?? $job['programme'];
        $application_deadline = $_POST['application_deadline'] ?? $job['application_deadline'];
        
        // Update job in database
        $update_sql = "UPDATE jobs SET 
                      job_Title = ?, 
                      job_Category = ?, 
                      job_Vacancy = ?, 
                      job_Description = ?,
                      job_Location = ?,
                      salary_estimation = ?,
                      programme = ?,
                      application_deadline = ?
                      WHERE job_ID = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssisssssi", $title, $category, $vacancy, $description, $location, $salary, $programme, $application_deadline, $job_id);
        
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
    <title>Edit Job - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/edit_job.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Top navigation with logo -->
    <nav class="top-nav">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" class="logo">
        </a>
    </nav>

    <div class="container">
        <h1>Edit Job Listing</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <!-- Job Details Section -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-briefcase"></i> Basic Job Information
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="title">Job Title</label>
                                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($job['job_Title']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="full_time" <?php if ($job['job_Category'] == 'full_time') echo 'selected'; ?>>Full Time</option>
                                    <option value="part_time" <?php if ($job['job_Category'] == 'part_time') echo 'selected'; ?>>Part Time</option>
                                    <option value="internship" <?php if ($job['job_Category'] == 'internship') echo 'selected'; ?>>Internship</option>
                                    <option value="contract" <?php if ($job['job_Category'] == 'contract') echo 'selected'; ?>>Contract</option>
                                    <option value="temporary" <?php if ($job['job_Category'] == 'temporary') echo 'selected'; ?>>Temporary</option>
                                    <option value="remote" <?php if ($job['job_Category'] == 'remote') echo 'selected'; ?>>Remote</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Position Details -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-map-marker-alt"></i> Position Details
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="vacancy">Number of Vacancies</label>
                                <input type="number" id="vacancy" name="vacancy" min="1" value="<?php echo htmlspecialchars($job['job_Vacancy']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="location">Job Location</label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($job['job_Location'] ?? ''); ?>" placeholder="e.g. Bandar Seri Begawan">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="salary">Salary Estimation</label>
                                <input type="text" id="salary" name="salary" value="<?php echo htmlspecialchars($job['salary_estimation'] ?? ''); ?>" placeholder="e.g. 1500-2000 BND or Negotiable">
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="application_deadline">Application Deadline</label>
                                <input type="date" id="application_deadline" name="application_deadline" value="<?php echo htmlspecialchars($job['application_deadline'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="programme">Target Programme</label>
                        <select id="programme" name="programme">
                            <option value="">Any Programme</option>
                            <!-- School of Business -->
                            <optgroup label="School of Business">
                                <option value="DBAF" <?php if (($job['programme'] ?? '') == 'DBAF') echo 'selected'; ?>>DIPLOMA IN BUSINESS ACCOUNTING & FINANCE</option>
                                <option value="DEMS" <?php if (($job['programme'] ?? '') == 'DEMS') echo 'selected'; ?>>DIPLOMA IN ENTREPRENEURSHIP & MARKETING STRATEGIES</option>
                                <option value="DHCM" <?php if (($job['programme'] ?? '') == 'DHCM') echo 'selected'; ?>>DIPLOMA IN HUMAN CAPITAL MANAGEMENT</option>
                                <option value="DAHMO" <?php if (($job['programme'] ?? '') == 'DAHMO') echo 'selected'; ?>>DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT</option>
                            </optgroup>
                            <!-- School of ICT -->
                            <optgroup label="School of Information and Communication Technology">
                                <option value="DAD" <?php if (($job['programme'] ?? '') == 'DAD') echo 'selected'; ?>>DIPLOMA IN APPLICATIONS DEVELOPMENT</option>
                                <option value="DCN" <?php if (($job['programme'] ?? '') == 'DCN') echo 'selected'; ?>>DIPLOMA IN CLOUD AND NETWORKING</option>
                                <option value="DDA" <?php if (($job['programme'] ?? '') == 'DDA') echo 'selected'; ?>>DIPLOMA IN DATA ANALYTICS</option>
                                <option value="DDAM" <?php if (($job['programme'] ?? '') == 'DDAM') echo 'selected'; ?>>DIPLOMA IN DIGITAL ARTS AND MEDIA</option>
                                <option value="DWT" <?php if (($job['programme'] ?? '') == 'DWT') echo 'selected'; ?>>DIPLOMA IN WEB TECHNOLOGY</option>
                            </optgroup>
                            <!-- Add more programmes as needed -->
                        </select>
                    </div>
                </div>
                
                <!-- Job Description -->
                <div class="form-section">
                    <h2 class="form-section-title">
                        <i class="fas fa-file-alt"></i> Job Description
                    </h2>
                    
                    <div class="form-group">
                        <label for="description">Provide detailed information about the job responsibilities, requirements, and benefits</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($job['job_Description']); ?></textarea>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="manage_jobs.php" class="cancel-btn"><i class="fas fa-times"></i> Cancel</a>
                    <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Update Job</button>
                </div>
            </form>
        </div>
    </div>

    <?php include_once '../../includes/floating-button.php'; ?>

    <script>
        // Set min date for application deadline to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('application_deadline').setAttribute('min', today);

        // Enhance textarea with automatic height adjustment
        const textarea = document.getElementById('description');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Trigger the input event on page load to adjust height
        window.addEventListener('load', function() {
            const event = new Event('input');
            textarea.dispatchEvent(event);
        });
    </script>
</body>
</html>