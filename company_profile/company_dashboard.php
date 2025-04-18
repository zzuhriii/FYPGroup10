<?php
// Start session if not already started
session_start();

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    // Redirect to login page if not logged in as company
    header("Location: /Website/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in company user ID

// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "marketing_day";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get company information
$company_sql = "SELECT * FROM users WHERE id = ? AND user_type = 'company'";
$company_stmt = $conn->prepare($company_sql);
$company_stmt->bind_param("i", $user_id);
$company_stmt->execute();
$company_result = $company_stmt->get_result();
$company = $company_result->fetch_assoc();

// Get company profile information
$profile_sql = "SELECT * FROM company_profile WHERE user_id = ?";  // Changed from id to user_id
$profile_stmt = $conn->prepare($profile_sql);
$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Get posted jobs count
$jobs_sql = "SELECT COUNT(*) as job_count FROM jobs WHERE company_id = ?";
$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("i", $user_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();
$jobs_data = $jobs_result->fetch_assoc();
$job_count = $jobs_data['job_count'] ?? 0;

// Get applications count
$applications_sql = "SELECT COUNT(*) as app_count FROM job_applications a 
                    JOIN jobs j ON a.job_id = j.job_ID 
                    WHERE j.company_id = ?";
$applications_stmt = $conn->prepare($applications_sql);
$applications_stmt->bind_param("i", $user_id);
$applications_stmt->execute();
$applications_result = $applications_stmt->get_result();
$applications_data = $applications_result->fetch_assoc();
$application_count = $applications_data['app_count'] ?? 0;

// Get recent applications
$recent_sql = "SELECT a.*, u.name as applicant_name, j.job_Title 
              FROM job_applications a 
              JOIN users u ON a.user_id = u.id 
              JOIN jobs j ON a.job_id = j.job_ID 
              WHERE j.company_id = ? 
              ORDER BY a.application_date DESC LIMIT 5";
$recent_stmt = $conn->prepare($recent_sql);
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_result = $recent_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/company_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>
    
    <!-- Navigation Links with Dropdown -->
    <div class="container">
        <div class="nav-links">
            <a href="/Website/jobs/browse_jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
            <a href="/Website/about.php"><i class="fas fa-info-circle"></i> About</a>
            <a href="/Website/contact.php"><i class="fas fa-envelope"></i> Contact</a>
            <a href="/Website/company_profile/location_map.php"><i class="fas fa-map-marker-alt"></i> Locations</a>
            <a href="/Website/jobs/manage_queue.php"><i class="fas fa-users"></i> Manage Queue</a>
            <a href="/Website/authentication/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="company-info">
                <div class="company-logo">
                    <?php if (!empty($profile['logo'])): ?>
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['logo']); ?>" alt="Company Logo">
                    <?php else: ?>
                        <i class="fas fa-building"></i>
                    <?php endif; ?>
                </div>
                <div class="company-details">
                    <h1><?php echo htmlspecialchars($company['name'] ?? 'Company Name'); ?></h1>
                    <p><?php echo htmlspecialchars($profile['tagline'] ?? 'Your company tagline'); ?></p>
                </div>
            </div>
            <div class="dashboard-actions">
                <a href="edit_profile.php" class="dashboard-btn primary-btn"><i class="fas fa-edit"></i> Edit Profile</a>
                <a href="companyprofile.php?id=<?php echo $user_id; ?>" class="dashboard-btn secondary-btn"><i class="fas fa-eye"></i> View Public Profile</a>
            </div>
        </div>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-briefcase"></i>
                <h2><?php echo $job_count; ?></h2>
                <p>Jobs Posted</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <h2><?php echo $application_count; ?></h2>
                <p>Applications Received</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <h2>0</h2>
                <p>Positions Filled</p>
            </div>
        </div>
        
        <!-- Add a welcome message and summary section -->
        <div class="section welcome-section" id="welcomeMessage" style="margin-bottom: 20px; position: relative;">
            <button onclick="dismissWelcome()" class="dismiss-btn" style="position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer; font-size: 16px; color: #999;">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="section-title">Welcome, <?php echo htmlspecialchars($company['name'] ?? 'Company'); ?>!</h3>
            <p>This is your company dashboard where you can manage your profile, job postings, and review applications from potential candidates.</p>
            
            <?php if ($job_count == 0): ?>
            <div class="alert" style="background-color: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin-top: 15px;">
                <i class="fas fa-info-circle"></i> You haven't posted any jobs yet. <a href="/Website/jobs/company/post_job.php" style="color: #0c5460; text-decoration: underline;">Post your first job</a> to start receiving applications!
            </div>
            <?php endif; ?>
        </div>
        
        <script>
            // Check if welcome message was previously dismissed
            document.addEventListener('DOMContentLoaded', function() {
                if (localStorage.getItem('welcomeDismissed') === 'true') {
                    document.getElementById('welcomeMessage').style.display = 'none';
                }
            });
            
            // Function to dismiss welcome message
            function dismissWelcome() {
                document.getElementById('welcomeMessage').style.display = 'none';
                localStorage.setItem('welcomeDismissed', 'true');
            }
        </script>
        
        <div class="dashboard-sections">
            <div class="main-content">
                <div class="section">
                    <h3 class="section-title">
                        Recent Applications
                        <a href="/Website/jobs/company/view_applications.php">View All</a>
                    </h3>
                    
                    <?php if ($recent_result->num_rows > 0): ?>
                        <?php while ($app = $recent_result->fetch_assoc()): ?>
                            <div class="application-item">
                                <div class="application-header">
                                    <div class="application-title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
                                    <div class="application-date"><?php echo date('M d, Y', strtotime($app['application_date'])); ?></div>
                                </div>
                                <div class="application-meta">
                                    <div class="applicant-name"><?php echo htmlspecialchars($app['applicant_name']); ?></div>
                                    <span class="application-status status-<?php echo strtolower($app['status']); ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </div>
                                <div style="margin-top: 10px;">
                                    <a href="/Website/jobs/company/view_application.php?id=<?php echo $app['id']; ?>" class="dashboard-btn secondary-btn" style="font-size: 12px; padding: 5px 10px;">View Details</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No applications received yet.</p>
                    <?php endif; ?>
                </div>
                
                <div class="section">
                    <h3 class="section-title">
                        Your Jobs
                        <a href="/Website/jobs/company/manage_jobs.php">Manage Jobs</a>
                    </h3>
                    <a href="/Website/jobs/company/post_job.php" class="dashboard-btn primary-btn" style="margin-bottom: 20px;">
                        <i class="fas fa-plus"></i> Post New Job
                    </a>
                    
                    <!-- Job listings would go here -->
                    <p>Manage your job listings and create new opportunities for graduates.</p>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="section">
                    <h3 class="section-title">Quick Links</h3>
                    <ul class="quick-links">
                        <li>
                            <a href="/Website/jobs/company/post_job.php">
                                <i class="fas fa-plus-circle"></i> Post a New Job
                            </a>
                        </li>
                        <li>
                            <a href="/Website/jobs/company/manage_jobs.php">
                                <i class="fas fa-tasks"></i> Manage Jobs
                            </a>
                        </li>
                        <li>
                            <a href="edit_profile.php">
                                <i class="fas fa-user-edit"></i> Edit Company Profile
                            </a>
                        </li>
                        <li>
                            <a href="manage_location.php">
                                <i class="fas fa-map-marker-alt"></i> Manage Location
                            </a>
                        </li>
                        <li>
                            <a href="/Website/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="section">
                    <h3 class="section-title">Profile Completion</h3>
                    <?php
                    // Calculate profile completion percentage
                    $total_fields = 10; // Total number of profile fields
                    $filled_fields = 0;
                    
                    if (!empty($profile['company_name'])) $filled_fields++;
                    if (!empty($profile['tagline'])) $filled_fields++;
                    if (!empty($profile['location'])) $filled_fields++;
                    if (!empty($profile['contact_info'])) $filled_fields++;
                    if (!empty($profile['founding_date'])) $filled_fields++;
                    if (!empty($profile['mission'])) $filled_fields++;
                    if (!empty($profile['vision'])) $filled_fields++;
                    if (!empty($profile['products'])) $filled_fields++;
                    if (!empty($profile['about_us'])) $filled_fields++;
                    if (!empty($profile['logo'])) $filled_fields++;
                    
                    $completion_percentage = ($filled_fields / $total_fields) * 100;
                    ?>
                    
                    <div class="profile-completion">
                        <p><?php echo round($completion_percentage); ?>% Complete</p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $completion_percentage; ?>%;"></div>
                        </div>
                        <?php if ($completion_percentage < 100): ?>
                            <p style="margin-top: 10px; font-size: 13px;">Complete your profile to attract more applicants!</p>
                            <a href="edit_profile.php" class="dashboard-btn primary-btn" style="margin-top: 10px; width: 100%; text-align: center;">
                                Complete Profile
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
    
    <!-- Include JavaScript file -->
    <script src="/Website/assets/js/company_dashboard.js"></script>
</body>
</html>