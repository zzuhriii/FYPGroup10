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
    
    $application_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Get application details with user and job information
    $app_sql = "SELECT a.*, u.*, j.job_Title, j.job_Category 
               FROM job_applications a 
               JOIN users u ON a.user_id = u.id 
               JOIN jobs j ON a.job_id = j.job_ID 
               WHERE a.id = '$application_id'";
    $app_result = mysqli_query($conn, $app_sql);
    
    if (mysqli_num_rows($app_result) == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $app = mysqli_fetch_assoc($app_result);
    
    // Comment out the debugging line
    // echo "<pre>"; print_r($app); echo "</pre>";
    
    // Check if CV exists - update the path to match where CVs are actually stored
    $cv_filename = $app['cv'];
    $cv_path = "/Website/uploads/cvs/" . $cv_filename;
    
    // Remove debugging lines
    // echo "CV Path: " . $cv_path . "<br>";
    // echo "CV Exists: " . ($cv_exists ? "Yes" : "No") . "<br>";
    // echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
    // echo "Full Path: " . $_SERVER['DOCUMENT_ROOT'] . $cv_path . "<br>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        
        .application-header {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #4285f4;
        }
        
        .job-title {
            font-size: 1.6em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .applicant-name {
            font-size: 1.2em;
            color: #555;
            margin-bottom: 15px;
        }
        
        .meta-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
            color: #555;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-align: center;
            margin-top: 10px;
        }
        
        .pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .accepted {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .declined {
            background-color: #F8D7DA;
            color: #721C24;
        }
        
        .section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .cover-letter, .skills-content, .education-content {
            white-space: pre-line;
            line-height: 1.6;
            color: #333;
        }
        
        .contact-info {
            margin-bottom: 20px;
        }
        
        .contact-item {
            margin-bottom: 10px;
            color: black;
        }
        
        .contact-label {
            font-weight: 600;
            display: inline-block;
            width: 100px;
        }
        
        .cv-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .cv-btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: all 0.2s;
        }
        
        .view-btn {
            background-color: #4285f4;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #3367d6;
        }
        
        .download-btn {
            background-color: #34a853;
            color: white;
        }
        
        .download-btn:hover {
            background-color: #2d8e47;
        }
        
        .cv-preview {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .action-btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            text-align: center;
            transition: all 0.2s;
        }
        
        .accept-btn {
            background-color: #28a745;
            color: white;
            border: none;
        }
        
        .accept-btn:hover {
            background-color: #218838;
        }
        
        .decline-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .decline-btn:hover {
            background-color: #c82333;
        }
        
        .back-btn {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }
        
        .back-btn:hover {
            background-color: #e2e6ea;
        }
    </style>
</head>
<body>
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
                <a href="<?php echo $cv_path; ?>" target="_blank" class="cv-btn view-btn">View CV</a>
                <a href="<?php echo $cv_path; ?>" download class="cv-btn download-btn">Download CV</a>
            </div>
            <div class="cv-preview">
                <iframe src="<?php echo $cv_path; ?>" width="100%" height="500px"></iframe>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="view_applications.php?job_id=<?php echo $app['job_id']; ?>" class="action-btn back-btn">Back to Applications</a>
            
            <?php if ($app['status'] == 'pending'): ?>
            <div>
                <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=accept" class="action-btn accept-btn">Accept Application</a>
                <a href="respond_application.php?id=<?php echo $app['id']; ?>&action=decline" class="action-btn decline-btn">Decline Application</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>