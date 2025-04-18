<?php
// Start session
session_start();

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Database connection
require_once '../../includes/db.php';

$company_id = $_SESSION['user_id'];

// Get company details
$sql = "SELECT * FROM users WHERE id = ? AND user_type = 'company'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
$company = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $job_title = $_POST['job_title'];
    $job_description = $_POST['job_description'];
    $job_location = $_POST['job_location'];
    $job_vacancy = $_POST['job_vacancy'];
    $application_deadline = $_POST['application_deadline'];
    $programme = $_POST['programme'];
    $job_category = $_POST['job_category'];
    $salary_estimation = $_POST['salary_estimation'];

    // Insert job into database - using job_Location instead of job_location
    $sql = "INSERT INTO jobs (job_Title, job_Description, job_Location, job_Vacancy, application_deadline, programme, job_Created, company_id, job_category, salary_estimation) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssiss", $job_title, $job_description, $job_location, $job_vacancy, $application_deadline, $programme, $company_id, $job_category, $salary_estimation);
    
    if ($stmt->execute()) {
        $success_message = "Job posted successfully!";
    } else {
        $error_message = "Error posting job: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job - Politeknik Brunei</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/post_job.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>

    <header>
        <div class="header-content">
            <h1>Post a New Job</h1>
        </div>
    </header>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="job_title">Job Title:</label>
                <input type="text" id="job_title" name="job_title" required>
            </div>
            
            <div class="form-group">
                <label for="job_description">Job Description:</label>
                <textarea id="job_description" name="job_description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="job_location">Job Location:</label>
                <input type="text" id="job_location" name="job_location" required>
            </div>
            
            <div class="form-group">
                <label for="job_category">Job Category:</label>
                <select id="job_category" name="job_category" required>
                    <option value="">Select a category</option>
                    <option value="full_time">Full-time</option>
                    <option value="part_time">Part-time</option>
                    <option value="contract">Contract</option>
                    <option value="internship">Internship</option>
                    <option value="remote">Remote</option>
                    <option value="temporary">Temporary</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="job_vacancy">Number of Vacancies:</label>
                <input type="number" id="job_vacancy" name="job_vacancy" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="salary_estimation">Salary Estimation (BND):</label>
                <input type="text" id="salary_estimation" name="salary_estimation" placeholder="e.g. 1500-2000 per month or Negotiable">
                <small class="form-text">Provide an estimated salary range to attract qualified candidates.</small>
            </div>
            
            <div class="form-group">
                <label for="application_deadline">Application Deadline:</label>
                <input type="date" id="application_deadline" name="application_deadline" required>
            </div>
            
            <div class="form-group">
                <label for="programme">Target Programme (Optional):</label>
                <select id="programme" name="programme">
                    <option value="">Any Programme</option>
                    <!-- School of Business -->
                    <optgroup label="School of Business">
                        <option value="DBAF">DIPLOMA IN BUSINESS ACCOUNTING & FINANCE</option>
                        <option value="DEMS">DIPLOMA IN ENTREPRENEURSHIP & MARKETING STRATEGIES</option>
                        <option value="DHCM">DIPLOMA IN HUMAN CAPITAL MANAGEMENT</option>
                        <option value="DAHMO">DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS</option>
                    </optgroup>
                    <!-- School of Information and Communication Technology -->
                    <optgroup label="School of Information and Communication Technology">
                        <option value="DAD">DIPLOMA IN APPLICATIONS DEVELOPMENT</option>
                        <option value="DCN">DIPLOMA IN CLOUD AND NETWORKING</option>
                        <option value="DDA">DIPLOMA IN DATA ANALYTICS</option>
                        <option value="DDAM">DIGITAL ARTS AND MEDIA</option>
                        <option value="DWT">DIPLOMA IN WEB TECHNOLOGY</option>
                    </optgroup>
                    <!-- School of Health Sciences -->
                    <optgroup label="School of Health Sciences">
                        <option value="DHSN">DIPLOMA IN HEALTH SCIENCE (NURSING)</option>
                        <option value="DHSM">DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)</option>
                        <option value="DHSP">DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)</option>
                        <option value="DHSCT">DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)</option>
                        <option value="DHSPH">DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)</option>
                    </optgroup>
                    <!-- School of Architecture and Engineering -->
                    <optgroup label="School of Architecture and Engineering">
                        <option value="DA">DIPLOMA IN ARCHITECTURE</option>
                        <option value="DID">DIPLOMA IN INTERIOR DESIGN</option>
                        <option value="DCE">DIPLOMA IN CIVIL ENGINEERING</option>
                        <option value="DEE">DIPLOMA IN ELECTRICAL ENGINEERING</option>
                        <option value="DECE">DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING</option>
                        <option value="DME">DIPLOMA IN MECHANICAL ENGINEERING</option>
                        <option value="DPE">DIPLOMA IN PETROLEUM ENGINEERING</option>
                    </optgroup>
                </select>
                <small class="form-text">Select a specific programme if this job is particularly suited for graduates from that programme.</small>
            </div>
            
            <button type="submit" class="btn-submit">Post Job</button>
        </form>
        
        <a href="/Website/company_profile/company_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Politeknik Brunei.</p>
    </footer>
</body>
</html>