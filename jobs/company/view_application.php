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
    
    // At the top of the file, after fetching the application data
    $app = $app_result->fetch_assoc();
    
    // Check if CV exists and determine the correct path
    $cv_filename = $app['cv'];
    $possible_paths = [
        $_SERVER['DOCUMENT_ROOT'] . "/Website/user_profile/uploads/cv/" . $cv_filename,
        $_SERVER['DOCUMENT_ROOT'] . "/Website/uploads/cvs/" . $cv_filename,
        $_SERVER['DOCUMENT_ROOT'] . "/Website/uploads/cv/" . $cv_filename
    ];
    
    $cv_exists = false;
    $cv_path = "";
    $cv_absolute_path = "";
    
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            $cv_exists = true;
            $cv_absolute_path = $path;
            $cv_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
            break;
        }
    }
    
    // If CV exists but is not a PDF, convert it to PDF
    if ($cv_exists && pathinfo($cv_filename, PATHINFO_EXTENSION) != 'pdf') {
        // We'll keep the original file and just change how we display it
        // No conversion needed as we'll use PDF.js viewer instead
    }
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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 900px;
            margin: 80px auto 40px;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        
        .application-header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            position: relative;
        }
        
        .job-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .applicant-name {
            font-size: 18px;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .meta-info {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #6c757d;
        }
        
        .status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .accepted {
            background-color: #d4edda;
            color: #155724;
        }
        
        .declined, .rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .section {
            margin-bottom: 25px;
            background-color: #fff;
            border-radius: 6px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #eaeaea;
        }
        
        .section-title {
            background-color: #f8f9fa;
            padding: 12px 20px;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 1px solid #eaeaea;
            border-radius: 6px 6px 0 0;
        }
        
        .contact-info, .skills-content, .education-content, .cover-letter {
            padding: 20px;
            line-height: 1.6;
        }
        
        .contact-item {
            margin-bottom: 10px;
        }
        
        .contact-label {
            font-weight: 500;
            color: #6c757d;
            width: 120px;
            display: inline-block;
        }
        
        .cv-actions {
            padding: 15px 20px;
            display: flex;
            gap: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .cv-btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        
        .cv-btn i {
            margin-right: 5px;
        }
        
        .view-btn {
            background-color: #17a2b8;
        }
        
        .download-btn {
            background-color: #6c757d;
        }
        
        .cv-preview {
            padding: 0;
            border-radius: 0 0 6px 6px;
            overflow: hidden;
        }
        
        iframe {
            border: none;
            display: block;
        }
        
        /* Keep the existing action buttons styling */
    </style>
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: fixed; top: 15px; left: 15px; z-index: 1000;">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
        </a>
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
                <a href="<?php echo $cv_path; ?>" target="_blank" class="cv-btn view-btn">
                    <i class="fas fa-eye"></i> View CV
                </a>
                <a href="<?php echo $cv_path; ?>" download class="cv-btn download-btn">
                    <i class="fas fa-download"></i> Download CV
                </a>
            </div>
            <div class="cv-preview">
                <?php
                if ($cv_exists) {
                    // Use direct embedding with fallback options
                    echo '<div style="height:600px; width:100%;">';
                    echo '<embed src="' . $cv_path . '" type="application/pdf" width="100%" height="100%">';
                    echo '</div>';
                    
                    // Add a fallback message
                    echo '<div style="margin-top:20px; padding:15px; background-color:#f8f9fa; text-align:center;">
                        <p>If the document doesn\'t display properly above, you can:</p>
                        <a href="' . $cv_path . '" target="_blank" style="color:#3498db; text-decoration:underline; margin-right:15px;">
                            <i class="fas fa-external-link-alt"></i> Open in new tab
                        </a>
                        <a href="' . $cv_path . '" download style="color:#3498db; text-decoration:underline;">
                            <i class="fas fa-download"></i> Download the file
                        </a>
                    </div>';
                } else {
                    echo '<div style="padding:20px; text-align:center; background-color:#f8f9fa;">
                        <p><i class="fas fa-exclamation-triangle" style="color:#dc3545;"></i> 
                        The CV file could not be found at any of the expected locations.</p>
                        <p>Filename: ' . htmlspecialchars($cv_filename) . '</p>
                        <p>Please check that the file exists in one of these directories:</p>
                        <ul style="text-align:left; display:inline-block;">
                            <li>/Website/user_profile/uploads/cv/</li>
                            <li>/Website/uploads/cvs/</li>
                            <li>/Website/uploads/cv/</li>
                        </ul>
                    </div>';
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="view_applications.php?job_id=<?php echo $app['job_id']; ?>" class="action-btn back-btn">
                <i class="fas fa-arrow-left"></i> Back to Applications
            </a>
            
            <?php if ($app['status'] == 'pending'): ?>
            <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=accept" class="action-btn accept-btn">
                <i class="fas fa-check"></i> Accept Application
            </a>
            <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=decline" class="action-btn decline-btn">
                <i class="fas fa-times"></i> Decline Application
            </a>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            gap: 20px; /* Adds space between buttons */
        }
        
        .action-btn {
            padding: 8px 15px; /* Smaller padding */
            font-size: 14px; /* Smaller font size */
            border-radius: 4px;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .back-btn {
            background-color: #6c757d;
        }
        
        .accept-btn {
            background-color: #28a745;
        }
        
        .decline-btn {
            background-color: #dc3545;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
    </style>
</body>
</html>