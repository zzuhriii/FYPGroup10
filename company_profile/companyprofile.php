<?php
// Start session if not already started
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get company ID from URL parameter
$company_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided and user is logged in as company, use their ID
if ($company_id === 0 && isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'company') {
    $company_id = $_SESSION['user_id'];
}

// If still no ID, redirect to companies list
if ($company_id === 0) {
    header("Location: /Website/companies.php");
    exit();
}

// Get company information
$company_sql = "SELECT u.*, cp.* 
                FROM users u 
                LEFT JOIN company_profile cp ON u.id = cp.user_id 
                WHERE u.id = ? AND u.user_type = 'company'";
$company_stmt = $conn->prepare($company_sql);
$company_stmt->bind_param("i", $company_id);
$company_stmt->execute();
$company_result = $company_stmt->get_result();

if ($company_result->num_rows === 0) {
    // Company not found, redirect to companies list
    header("Location: /Website/companies.php");
    exit();
}

$company = $company_result->fetch_assoc();

// Get company location
// Make this section optional to prevent fatal errors
try {
    $location_sql = "SELECT * FROM company_location WHERE user_id = ?";
    $location_stmt = $conn->prepare($location_sql);
    $location_stmt->bind_param("i", $company_id);
    $location_stmt->execute();
    $location_result = $location_stmt->get_result();
    $location = $location_result->fetch_assoc();
} catch (Exception $e) {
    // If table doesn't exist or any other error, set location to null
    $location = null;
}

// Get company's active job listings
$jobs_sql = "SELECT * FROM jobs WHERE company_id = ?";
$jobs_stmt = $conn->prepare($jobs_sql);
$jobs_stmt->bind_param("i", $company_id);
$jobs_stmt->execute();
$jobs_result = $jobs_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .company-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .company-logo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 30px;
            overflow: hidden;
        }
        
        .company-logo img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .company-logo i {
            font-size: 60px;
            color: #aaa;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .company-tagline {
            font-size: 18px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .company-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            color: #666;
        }
        
        .meta-item i {
            margin-right: 8px;
            color: #4285f4;
        }
        
        .company-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .section {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .company-description {
            line-height: 1.6;
            color: #555;
        }
        
        .map-container {
            height: 300px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        #map {
            height: 100%;
            width: 100%;
        }
        
        .job-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .job-card {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .job-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .job-meta-item {
            display: flex;
            align-items: center;
        }
        
        .job-meta-item i {
            margin-right: 5px;
            color: #4285f4;
        }
        
        .job-description {
            margin-bottom: 15px;
            font-size: 14px;
            color: #555;
            line-height: 1.5;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #4285f4;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3367d6;
        }
        
        .contact-info {
            margin-top: 20px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .contact-item i {
            margin-right: 10px;
            color: #4285f4;
            margin-top: 3px;
        }
        
        .contact-item a {
            color: #4285f4;
            text-decoration: none;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .company-header {
                flex-direction: column;
                text-align: center;
            }
            
            .company-logo {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .company-sections {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>
    
    <div class="container">
        <!-- Back button -->
        <a href="javascript:history.back()" class="btn" style="margin-bottom: 20px; background-color: #f0f0f0; color: #333;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        
        <!-- View Full Company Profile Button -->
        <a href="/Website/company_profile/view_profile.php?company_id=<?php echo $company_id; ?>" class="btn" style="margin-bottom: 20px; margin-left: 10px; background-color: #4285f4; color: white;">
            <i class="fas fa-id-card"></i> View Full Company Profile
        </a>
        
        <div class="company-header">
            <div class="company-logo">
                <?php if (!empty($company['logo'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($company['logo']); ?>" alt="<?php echo htmlspecialchars($company['name']); ?> Logo">
                <?php else: ?>
                    <i class="fas fa-building"></i>
                <?php endif; ?>
            </div>
            <div class="company-info">
                <h1 class="company-name"><?php echo htmlspecialchars($company['name']); ?></h1>
                <div class="company-tagline"><?php echo htmlspecialchars($company['tagline'] ?? ''); ?></div>
                
                <div class="company-meta">
                    <?php if (!empty($company['industry'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-industry"></i>
                            <span><?php echo htmlspecialchars($company['industry']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($company['size'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span><?php echo htmlspecialchars($company['size']); ?> employees</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($company['website'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-globe"></i>
                            <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank"><?php echo htmlspecialchars($company['website']); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="company-sections">
            <div class="main-content">
                <?php if (!empty($company['description'])): ?>
                    <div class="section">
                        <h2 class="section-title">About <?php echo htmlspecialchars($company['name']); ?></h2>
                        <div class="company-description">
                            <?php echo nl2br(htmlspecialchars($company['description'])); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="section">
                    <h2 class="section-title">Open Positions</h2>
                    <?php if ($jobs_result->num_rows > 0): ?>
                        <div class="job-list">
                            <?php while ($job = $jobs_result->fetch_assoc()): ?>
                                <div class="job-card">
                                    <h3 class="job-title">
                                        <?php 
                                        // Check for job_Title (uppercase T) or job_title (lowercase t)
                                        if (!empty($job['job_Title'])) {
                                            echo htmlspecialchars($job['job_Title']);
                                        } elseif (!empty($job['job_title'])) {
                                            echo htmlspecialchars($job['job_title']);
                                        } else {
                                            echo 'Job Position';
                                        }
                                        ?>
                                    </h3>
                                    
                                    <!-- Job preview section -->
                                    <div class="job-preview" style="margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
                                        <?php if (!empty($job['job_Title']) || !empty($job['job_title'])): ?>
                                            <div class="preview-item" style="margin-bottom: 8px;">
                                                <strong>Title:</strong> 
                                                <span style="font-weight: 500; color: #333;">
                                                    <?php 
                                                    if (!empty($job['job_Title'])) {
                                                        echo htmlspecialchars($job['job_Title']);
                                                    } elseif (!empty($job['job_title'])) {
                                                        echo htmlspecialchars($job['job_title']);
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($job['job_description'])): ?>
                                            <div class="preview-item">
                                                <strong>Description:</strong> 
                                                <?php echo htmlspecialchars(substr($job['job_description'], 0, 150) . (strlen($job['job_description']) > 150 ? '...' : '')); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($job['programme'])): ?>
                                            <div class="preview-item" style="margin-top: 8px;">
                                                <strong>Programme:</strong> 
                                                <?php 
                                                $programme_codes = [
                                                    'DBAF' => 'DIPLOMA IN BUSINESS ACCOUNTING & FINANCE',
                                                    'DEMS' => 'DIPLOMA IN ENTREPRENEURSHIP & MARKETING STRATEGIES',
                                                    'DHCM' => 'DIPLOMA IN HUMAN CAPITAL MANAGEMENT',
                                                    'DAHMO' => 'DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS',
                                                    'DAD' => 'DIPLOMA IN APPLICATIONS DEVELOPMENT',
                                                    'DCN' => 'DIPLOMA IN CLOUD AND NETWORKING',
                                                    'DDA' => 'DIPLOMA IN DATA ANALYTICS',
                                                    'DDAM' => 'DIGITAL ARTS AND MEDIA',
                                                    'DWT' => 'DIPLOMA IN WEB TECHNOLOGY',
                                                    'DHSN' => 'DIPLOMA IN HEALTH SCIENCE (NURSING)',
                                                    'DHSM' => 'DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)',
                                                    'DHSP' => 'DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)',
                                                    'DHSCT' => 'DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)',
                                                    'DHSPH' => 'DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)',
                                                    'DA' => 'DIPLOMA IN ARCHITECTURE',
                                                    'DID' => 'DIPLOMA IN INTERIOR DESIGN',
                                                    'DCE' => 'DIPLOMA IN CIVIL ENGINEERING',
                                                    'DEE' => 'DIPLOMA IN ELECTRICAL ENGINEERING',
                                                    'DECE' => 'DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING',
                                                    'DME' => 'DIPLOMA IN MECHANICAL ENGINEERING',
                                                    'DPE' => 'DIPLOMA IN PETROLEUM ENGINEERING'
                                                ];
                                                
                                                echo htmlspecialchars(isset($programme_codes[$job['programme']]) ? 
                                                    $programme_codes[$job['programme']] : $job['programme']);
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="preview-meta" style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px;">
                                            <?php if (!empty($job['job_category'])): ?>
                                                <div class="preview-meta-item">
                                                    <i class="fas fa-tag"></i>
                                                    <span><?php echo htmlspecialchars($job['job_category']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($job['job_location'])): ?>
                                                <div class="preview-meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span><?php echo htmlspecialchars($job['job_location']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($job['application_deadline'])): ?>
                                                <div class="preview-meta-item">
                                                    <i class="fas fa-clock"></i>
                                                    <span>Deadline: <?php echo htmlspecialchars($job['application_deadline']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="job-meta">
                                        <!-- Existing job meta items remain unchanged -->
                                    </div>
                                    
                                    <div class="job-actions" style="display: flex; gap: 10px; margin-top: 15px;">
                                        <?php if (!empty($job['job_id']) || !empty($job['job_ID'])): ?>
                                            <?php 
                                            // Get the job ID regardless of case
                                            $job_id = !empty($job['job_id']) ? $job['job_id'] : $job['job_ID'];
                                            ?>
                                            <a href="/Website/jobs/view_job.php?id=<?php echo $job_id; ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-primary" disabled>
                                                <i class="fas fa-eye"></i> Details Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>No open positions at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="sidebar">
                <div class="section">
                    <h2 class="section-title">Contact Information</h2>
                    <div class="contact-info">
                        <?php if (!empty($company['email'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($company['email']); ?>"><?php echo htmlspecialchars($company['email']); ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($company['phone'])): ?>
                            <div class="contact-item">
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo htmlspecialchars($company['phone']); ?>"><?php echo htmlspecialchars($company['phone']); ?></a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($location)): ?>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($location['address']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($location)): ?>
                    <div class="section">
                        <h2 class="section-title">Location</h2>
                        <div class="map-container">
                            <div id="map"></div>
                        </div>
                        <a href="https://www.openstreetmap.org/directions?from=&to=<?php echo $location['latitude']; ?>%2C<?php echo $location['longitude']; ?>" 
                           target="_blank" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-directions"></i> Get Directions
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($location)): ?>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize the map
                const map = L.map('map').setView([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], 15);
                
                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                // Add marker for company location
                const marker = L.marker([<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>], {
                    title: "<?php echo htmlspecialchars($company['name']); ?>"
                }).addTo(map);
                
                // Create popup content
                const popupContent = `
                    <div style="max-width: 200px; padding: 10px;">
                        <h3 style="margin-top: 0; color: #4285f4;"><?php echo htmlspecialchars($company['name']); ?></h3>
                        <p style="margin-bottom: 5px;"><?php echo htmlspecialchars($location['address']); ?></p>
                        <a href="https://www.openstreetmap.org/directions?from=&to=<?php echo $location['latitude']; ?>%2C<?php echo $location['longitude']; ?>" 
                           target="_blank" style="color: #4285f4; text-decoration: none;">
                           <i class="fas fa-directions"></i> Get Directions
                        </a>
                    </div>
                `;
                
                // Add popup to marker
                marker.bindPopup(popupContent);
            });
        </script>
    <?php endif; ?>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
</body>
</html>