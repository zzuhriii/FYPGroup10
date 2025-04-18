<?php
// Start session if not already started
session_start();

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

// Check if company_id is provided
if (!isset($_GET['company_id']) || empty($_GET['company_id'])) {
    echo "<div class='error-message'>No company specified.</div>";
    exit();
}

$company_id = mysqli_real_escape_string($conn, $_GET['company_id']);

// Get company information
$company_sql = "SELECT * FROM users WHERE id = ? AND user_type = 'company'";
$stmt = $conn->prepare($company_sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company_result = $stmt->get_result();

if ($company_result->num_rows == 0) {
    echo "<div class='error-message'>Company not found.</div>";
    exit();
}

$company = $company_result->fetch_assoc();

// Get company profile data
$profile_sql = "SELECT * FROM company_profile WHERE user_id = ?";
$stmt = $conn->prepare($profile_sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

// If no profile exists, show basic info only
if (!$profile) {
    $profile = [
        'company_name' => $company['name'],
        'tagline' => '',
        'location' => '',
        'contact_info' => '',
        'about_us' => 'No detailed information available for this company yet.'
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['company_name']); ?> - Company Profile</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/company_profile_view.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: fixed; top: 10px; left: 10px; z-index: 1000;">
        <a href="/Website/index.php">
            <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
        </a>
    </div>

    <div class="company-profile-container">
        <div class="company-header">
            <?php if (!empty($profile['logo'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['logo']); ?>" alt="<?php echo htmlspecialchars($profile['company_name']); ?> Logo" class="company-logo">
            <?php else: ?>
                <div class="company-logo">No Logo</div>
            <?php endif; ?>
            
            <div class="company-info">
                <h1><?php echo htmlspecialchars($profile['company_name']); ?></h1>
                <?php if (!empty($profile['tagline'])): ?>
                    <div class="company-tagline"><?php echo htmlspecialchars($profile['tagline']); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($profile['location'])): ?>
                    <div class="company-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($profile['location']); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($profile['contact_info'])): ?>
                    <div class="company-contact"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($profile['contact_info']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- About Us Section -->
        <div class="profile-section">
            <h2>About Us</h2>
            <?php if (!empty($profile['about_us'])): ?>
                <p><?php echo nl2br(htmlspecialchars($profile['about_us'])); ?></p>
            <?php else: ?>
                <p class="no-data">No information available.</p>
            <?php endif; ?>
        </div>
        
        <!-- Mission & Vision Section -->
        <?php if (!empty($profile['mission']) || !empty($profile['vision'])): ?>
        <div class="profile-section">
            <h2>Mission & Vision</h2>
            <?php if (!empty($profile['mission'])): ?>
                <h3>Our Mission</h3>
                <p><?php echo nl2br(htmlspecialchars($profile['mission'])); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile['vision'])): ?>
                <h3>Our Vision</h3>
                <p><?php echo nl2br(htmlspecialchars($profile['vision'])); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- History Section -->
        <?php if (!empty($profile['founding_date']) || !empty($profile['founders']) || !empty($profile['milestones'])): ?>
        <div class="profile-section">
            <h2>Company History</h2>
            
            <?php if (!empty($profile['founding_date'])): ?>
                <p><strong>Founded:</strong> <?php echo htmlspecialchars($profile['founding_date']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile['founders'])): ?>
                <p><strong>Founders:</strong> <?php echo htmlspecialchars($profile['founders']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile['milestones'])): ?>
                <h3>Key Milestones</h3>
                <ul class="company-milestones">
                    <?php 
                    $milestones = explode("\n", $profile['milestones']);
                    foreach ($milestones as $milestone) {
                        if (trim($milestone) !== '') {
                            echo "<li>" . htmlspecialchars($milestone) . "</li>";
                        }
                    }
                    ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Products & Services Section -->
        <?php if (!empty($profile['products']) || !empty($profile['usp'])): ?>
        <div class="profile-section">
            <h2>Products & Services</h2>
            
            <?php if (!empty($profile['usp'])): ?>
                <p><strong>Our Unique Value:</strong> <?php echo htmlspecialchars($profile['usp']); ?></p>
            <?php endif; ?>
            
            <?php if (!empty($profile['products'])): ?>
                <h3>What We Offer</h3>
                <ul class="company-products">
                    <?php 
                    $products = explode("\n", $profile['products']);
                    foreach ($products as $product) {
                        if (trim($product) !== '') {
                            echo "<li>" . htmlspecialchars($product) . "</li>";
                        }
                    }
                    ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Achievements Section -->
        <?php if (!empty($profile['awards']) || !empty($profile['testimonials'])): ?>
        <div class="profile-section">
            <h2>Achievements & Recognition</h2>
            
            <?php if (!empty($profile['awards'])): ?>
                <h3>Awards & Certifications</h3>
                <ul class="company-awards">
                    <?php 
                    $awards = explode("\n", $profile['awards']);
                    foreach ($awards as $award) {
                        if (trim($award) !== '') {
                            echo "<li>" . htmlspecialchars($award) . "</li>";
                        }
                    }
                    ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($profile['testimonials'])): ?>
                <h3>What Others Say About Us</h3>
                <div class="testimonials">
                    <?php 
                    $testimonials = explode("\n", $profile['testimonials']);
                    foreach ($testimonials as $testimonial) {
                        if (trim($testimonial) !== '') {
                            echo "<blockquote>" . htmlspecialchars($testimonial) . "</blockquote>";
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="javascript:history.back()" class="back-button"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
    </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
</body>
</html>