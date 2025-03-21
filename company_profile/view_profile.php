<?php
// Include header
// include $_SERVER['DOCUMENT_ROOT'] . '/Website/header.php';

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
$company_sql = "SELECT * FROM users WHERE id = '$company_id' AND user_type = 'company'";
$company_result = mysqli_query($conn, $company_sql);

if (!$company_result || mysqli_num_rows($company_result) == 0) {
    echo "<div class='error-message'>Company not found.</div>";
    exit();
}

$company = mysqli_fetch_assoc($company_result);

// Get company profile data
$profile_sql = "SELECT * FROM company_profile WHERE user_id = '$company_id'";
$profile_result = mysqli_query($conn, $profile_sql);
$profile = mysqli_fetch_assoc($profile_result);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['company_name']); ?> - Company Profile</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <style>
        .company-profile-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .company-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .company-logo {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: contain;
            background-color: #f5f5f5;
            margin-right: 20px;
        }
        
        .company-info h1 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        .company-tagline {
            font-style: italic;
            color: #666;
            margin-bottom: 10px;
        }
        
        .company-location, .company-contact {
            color: #555;
            margin-bottom: 5px;
        }
        
        .profile-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .profile-section p {
            line-height: 1.6;
            color: #333;
        }
        
        .company-milestones, .company-products, .company-awards {
            list-style-type: none;
            padding-left: 0;
        }
        
        .company-milestones li, .company-products li, .company-awards li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .company-milestones li:last-child, .company-products li:last-child, .company-awards li:last-child {
            border-bottom: none;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #2980b9;
        }
        
        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
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
                    <div class="company-location">📍 <?php echo htmlspecialchars($profile['location']); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($profile['contact_info'])): ?>
                    <div class="company-contact">📞 <?php echo htmlspecialchars($profile['contact_info']); ?></div>
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
        
        <a href="javascript:history.back()" class="back-button">Back to Jobs</a>
    </div>
</body>
</html>