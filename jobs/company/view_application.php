<?php
   // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if application ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $application_id = $_GET['id'];
    
    // Get application details with user and job information using prepared statement
    $app_sql = "SELECT a.*, u.*, j.job_Title, j.job_Category 
               FROM job_applications a 
               JOIN users u ON a.user_id = u.id 
               JOIN jobs j ON a.job_id = j.job_ID 
               WHERE a.id = ?";
    
    $stmt = $conn->prepare($app_sql);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $app_result = $stmt->get_result();
    
    if ($app_result->num_rows == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $app = $app_result->fetch_assoc();
    
    // Check if CV exists - update the path to match where CVs are actually stored
    $cv_filename = $app['cv'];
    $cv_path = "/Website/uploads/cvs/" . $cv_filename;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/view_application.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Application Details</h1>
        
        <div class="application-header">
            <div class="job-title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
            <div class="applicant-name">Applicant: <?php echo htmlspecialchars($app['name']); ?></div>
            <div class="meta-info">
                <span>Category: <?php echo htmlspecialchars($app['job_Category']); ?></span>
                <span>Applied: <?php echo date('M d, Y', strtotime($app['application_date'])); ?></span>
            </div>
            <span class="status <?php echo strtolower($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span>
        </div>
        
        <div class="section">
            <div class="section-title">Contact Information</div>
            <div class="contact-info">
                <div class="contact-item">
                    <span class="contact-label">Name:</span> <?php echo htmlspecialchars($app['name']); ?>
                </div>
                <div class="contact-item">
                    <span class="contact-label">Email:</span> <?php echo htmlspecialchars($app['email']); ?>
                </div>
                <?php if (!empty($app['phone'])): ?>
                <div class="contact-item">
                    <span class="contact-label">Phone:</span> <?php echo htmlspecialchars($app['phone']); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($app['ic_number'])): ?>
                <div class="contact-item">
                    <span class="contact-label">IC Number:</span> <?php echo htmlspecialchars($app['ic_number']); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($app['programme'])): ?>
                <div class="contact-item">
                    <span class="contact-label">Programme:</span> <?php echo htmlspecialchars($app['programme']); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($app['user_type'])): ?>
                <div class="contact-item">
                    <span class="contact-label">User Type:</span> <?php echo ucfirst(htmlspecialchars($app['user_type'])); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($app['skills'])): ?>
        <div class="section">
            <div class="section-title">Skills</div>
            <div class="skills-content">
                <?php echo nl2br(htmlspecialchars($app['skills'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($app['education'])): ?>
        <div class="section">
            <div class="section-title">Education</div>
            <div class="education-content">
                <?php echo nl2br(htmlspecialchars($app['education'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-title">Cover Letter</div>
            <div class="cover-letter">
                <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
            </div>
        </div>
        
        <?php if (!empty($app['cv'])): ?>
        <div class="section cv-section">
            <div class="section-title">Curriculum Vitae (CV)</div>
            <div class="cv-actions">
                <a href="/Website/user_profile/uploads/cv/<?php echo htmlspecialchars($app['cv']); ?>" target="_blank" class="cv-btn view-btn">
                    <i class="fas fa-eye"></i> View CV
                </a>
                <a href="/Website/user_profile/uploads/cv/<?php echo htmlspecialchars($app['cv']); ?>" download class="cv-btn download-btn">
                    <i class="fas fa-download"></i> Download CV
                </a>
            </div>
            <div class="cv-preview">
                <iframe src="/Website/user_profile/uploads/cv/<?php echo htmlspecialchars($app['cv']); ?>" width="100%" height="500px"></iframe>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="view_applications.php?job_id=<?php echo $app['job_id']; ?>" class="action-btn back-btn">
                <i class="fas fa-arrow-left"></i> Back to Applications
            </a>
            
            <?php if ($app['status'] == 'pending'): ?>
            <div>
                <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=accept" class="action-btn accept-btn">
                    <i class="fas fa-check"></i> Accept Application
                </a>
                <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=decline" class="action-btn decline-btn">
                    <i class="fas fa-times"></i> Decline Application
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>