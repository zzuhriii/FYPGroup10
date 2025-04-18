<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a graduate
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if application ID is provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: my_applications.php");
        exit();
    }
    
    $application_id = $_GET['id'];
    
    // Check if company_id column exists in jobs table
    $company_id_check = mysqli_query($conn, "SHOW COLUMNS FROM jobs LIKE 'company_id'");
    $has_company_id = mysqli_num_rows($company_id_check) > 0;
    
    // Prepare the SQL query based on table structure
    if ($has_company_id) {
        $app_sql = "SELECT a.*, j.job_Title, j.job_Category, j.job_Description, c.name as company_name 
                   FROM job_applications a 
                   JOIN jobs j ON a.job_id = j.job_ID 
                   JOIN users c ON j.company_id = c.id
                   WHERE a.id = ? AND a.user_id = ?";
        
        $stmt = $conn->prepare($app_sql);
        $stmt->bind_param("ii", $application_id, $user_id);
    } else {
        $app_sql = "SELECT a.*, j.job_Title, j.job_Category, j.job_Description, 'Company' as company_name 
                   FROM job_applications a 
                   JOIN jobs j ON a.job_id = j.job_ID 
                   WHERE a.id = ? AND a.user_id = ?";
        
        $stmt = $conn->prepare($app_sql);
        $stmt->bind_param("ii", $application_id, $user_id);
    }
    
    $stmt->execute();
    $app_result = $stmt->get_result();
    
    if ($app_result->num_rows == 0) {
        header("Location: my_applications.php");
        exit();
    }
    
    $app = $app_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/application_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <div class="container">
        <h1>Application Details</h1>
        
        <?php if ($app['status'] == 'accepted'): ?>
        <div class="status-message status-accepted">
            <p><strong>Congratulations!</strong> Your application has been accepted by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>They will contact you soon with further details about the next steps.</p>
        </div>
        <?php elseif ($app['status'] == 'declined'): ?>
        <div class="status-message status-declined">
            <p>We're sorry, but your application has been declined by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>Don't be discouraged - keep applying to other opportunities that match your skills and interests.</p>
            <?php if (!empty($app['decline_reason'])): ?>
            <p class="decline-reason">Reason: <?php echo htmlspecialchars($app['decline_reason']); ?></p>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="status-message status-pending">
            <p>Your application is currently under review by <?php echo htmlspecialchars($app['company_name']); ?>.</p>
            <p>You will be notified when there is an update to your application status.</p>
        </div>
        <?php endif; ?>
        
        <div class="application-header">
            <div class="job-title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
            <div class="company-name"><?php echo htmlspecialchars($app['company_name']); ?></div>
            <div class="meta-info">
                <span>Category: <?php echo htmlspecialchars($app['job_Category']); ?></span>
                <span>Applied: <?php echo date('M d, Y', strtotime($app['application_date'])); ?></span>
            </div>
            <span class="status <?php echo strtolower($app['status']); ?>"><?php echo ucfirst($app['status']); ?></span>
        </div>
        
        <div class="section">
            <div class="section-title">Job Description</div>
            <div class="job-description">
                <?php echo nl2br(htmlspecialchars($app['job_Description'])); ?>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Your Cover Letter</div>
            <div class="cover-letter">
                <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
            </div>
        </div>
        
        <div class="back-link">
            <a href="my_applications.php"><i class="fas fa-arrow-left"></i> Back to My Applications</a>
        </div>
    </div>
</body>
</html>