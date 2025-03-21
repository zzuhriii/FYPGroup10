<?php
    include 'header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    // Check if job ID is provided
    if (!isset($_GET['id'])) {
        header("Location: graduates_homepage.php");
        exit();
    }
    
    $job_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get job details with company information
    $sql = "SELECT j.*, c.name as company_name, c.email as company_email, c.phone as company_phone, c.description as company_description 
            FROM jobs j 
            JOIN companies c ON j.company_id = c.id 
            WHERE j.job_ID = '$job_id'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: graduates_homepage.php");
        exit();
    }
    
    $job = mysqli_fetch_assoc($result);
    
    // Check if user has already applied
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT * FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    $already_applied = mysqli_num_rows($check_result) > 0;
    
    if ($already_applied) {
        $application = mysqli_fetch_assoc($check_result);
        $application_status = $application['status'];
    }
?>
<body>
    <div class="job-details-container">
        <div class="back-link">
            <a href="graduates_homepage.php"><i class="fa fa-arrow-left"></i> Back to Jobs</a>
        </div>
        
        <div class="job-details-header">
            <h1><?php echo $job['job_Title']; ?></h1>
            <div class="company-badge">
                <span class="company-name"><?php echo $job['company_name']; ?></span>
            </div>
        </div>
        
        <div class="job-details-content">
            <div class="job-main-details">
                <div class="job-info-card">
                    <h3>Job Details</h3>
                    <div class="job-meta">
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value"><?php echo $job['job_Category']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Vacancies:</span>
                            <span class="meta-value"><?php echo $job['job_Vacancy']; ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Posted On:</span>
                            <span class="meta-value"><?php echo date('F j, Y', strtotime($job['job_Offered'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="job-description-section">
                    <h3>Job Description</h3>
                    <div class="description-content">
                        <?php echo nl2br($job['job_Description']); ?>
                    </div>
                </div>
                
                <div class="company-section">
                    <h3>About the Company</h3>
                    <div class="company-content">
                        <p><?php echo nl2br($job['company_description']); ?></p>
                    </div>
                </div>
                
                <div class="application-section">
                    <?php if ($already_applied): ?>
                        <div class="already-applied">
                            <h3>Application Status</h3>
                            <div class="status-badge <?php echo strtolower($application_status); ?>">
                                <?php echo ucfirst($application_status); ?>
                            </div>
                            <?php if ($application_status == 'declined' && !empty($application['feedback'])): ?>
                                <div class="feedback-section">
                                    <h4>Feedback from Employer:</h4>
                                    <p><?php echo nl2br($application['feedback']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <h3>Apply for this Position</h3>
                        <form action="apply_job.php" method="POST" class="application-form">
                            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                            <div class="form-group">
                                <label for="cover_letter">Cover Letter (Optional)</label>
                                <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Tell the employer why you're a good fit for this position..."></textarea>
                            </div>
                            <button type="submit" class="apply-btn">Submit Application</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="job-sidebar">
                <div class="company-contact-card">
                    <h3>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <span class="contact-label">Email:</span>
                            <span class="contact-value"><?php echo $job['company_email']; ?></span>
                        </div>
                        <div class="contact-item">
                            <span class="contact-label">Phone:</span>
                            <span class="contact-value"><?php echo $job['company_phone']; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="similar-jobs-card">
                    <h3>Similar Jobs</h3>
                    <div class="similar-jobs-list">
                        <?php
                            // Get similar jobs based on category
                            $category = $job['job_Category'];
                            $similar_sql = "SELECT job_ID, job_Title, company_id FROM jobs 
                                           WHERE job_Category = '$category' AND job_ID != '$job_id' 
                                           LIMIT 5";
                            $similar_result = mysqli_query($conn, $similar_sql);
                            
                            if (mysqli_num_rows($similar_result) > 0) {
                                while ($similar_job = mysqli_fetch_assoc($similar_result)) {
                                    echo "<div class='similar-job-item'>
                                        <a href='job_details.php?id=".$similar_job['job_ID']."'>".$similar_job['job_Title']."</a>
                                    </div>";
                                }
                            } else {
                                echo "<p>No similar jobs found.</p>";
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>