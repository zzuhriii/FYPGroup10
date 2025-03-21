<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: /Website/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

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

// Check if user is a company
$user_check_sql = "SELECT user_type FROM users WHERE id = ?";
$user_check_stmt = $conn->prepare($user_check_sql);
$user_check_stmt->bind_param("i", $user_id);
$user_check_stmt->execute();
$user_result = $user_check_stmt->get_result();

if ($user_result->num_rows === 0 || $user_result->fetch_assoc()['user_type'] !== 'company') {
    // Redirect if user is not found or not a company
    echo "<script>alert('Access denied. Only company accounts can edit company profiles.');</script>";
    echo "<script>window.location.href = '/Website/index.php';</script>";
    exit();
}

// Check if company profile exists for this user
try {
    // First check if the table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'company_profile'");
    
    if ($table_check->num_rows == 0) {
        // Table doesn't exist, create it
        $create_table_sql = "CREATE TABLE company_profile (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            company_name VARCHAR(255) DEFAULT 'Company Name',
            tagline VARCHAR(255),
            location VARCHAR(255),
            contact_info VARCHAR(255),
            logo MEDIUMBLOB,
            office_photo MEDIUMBLOB,
            infographic MEDIUMBLOB,
            founding_date VARCHAR(100),
            founders VARCHAR(255),
            milestones TEXT,
            mission TEXT,
            vision TEXT,
            products TEXT,
            usp VARCHAR(255),
            awards TEXT,
            testimonials TEXT,
            about_us TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table_sql);
    } else {
        // Table exists, check if user_id column exists
        $column_check = $conn->query("SHOW COLUMNS FROM company_profile LIKE 'user_id'");
        if ($column_check->num_rows == 0) {
            // Add user_id column
            $conn->query("ALTER TABLE company_profile ADD COLUMN user_id INT NOT NULL AFTER id");
        }
    }
    
    // Now check if profile exists for this user
    $check_sql = "SELECT * FROM company_profile WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    // If no profile exists, create one
    if ($result->num_rows === 0) {
        $create_sql = "INSERT INTO company_profile (user_id, company_name) VALUES (?, ?)";
        $create_stmt = $conn->prepare($create_sql);
        $company_name = "Company Name"; // Default company name
        $create_stmt->bind_param("is", $user_id, $company_name);
        $create_stmt->execute();
        
        // Refresh the result
        $check_stmt->execute();
        $result = $check_stmt->get_result();
    }
    
    // Load existing profile data
    $profile = $result->fetch_assoc();
    
} catch (Exception $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "');</script>";
    echo "<p>Please contact the administrator to fix this issue.</p>";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Determine which section was submitted
        $section = $_POST['section'] ?? '';

        // Prepare the SQL query based on the section
        switch ($section) {
            case 'visual_elements':
                $logo = null;
                $officePhoto = null;
                $infographic = null;

                if (!empty($_FILES['logoUpload']['tmp_name'])) {
                    $logo = file_get_contents($_FILES['logoUpload']['tmp_name']);
                }
                if (!empty($_FILES['officePhotoUpload']['tmp_name'])) {
                    $officePhoto = file_get_contents($_FILES['officePhotoUpload']['tmp_name']);
                }
                if (!empty($_FILES['infographicUpload']['tmp_name'])) {
                    $infographic = file_get_contents($_FILES['infographicUpload']['tmp_name']);
                }

                // Update SQL to include WHERE clause for user_id
                $sql = "UPDATE company_profile SET logo = ?, office_photo = ?, infographic = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $logo, $officePhoto, $infographic, $user_id);
                break;

            case 'company_overview':
                $companyName = $_POST['companyName'] ?? '';
                $tagline = $_POST['tagline'] ?? '';
                $location = $_POST['location'] ?? '';
                $contactInfo = $_POST['contactInfo'] ?? '';

                $sql = "UPDATE company_profile SET company_name = ?, tagline = ?, location = ?, contact_info = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $companyName, $tagline, $location, $contactInfo, $user_id);
                break;

            case 'history_background':
                $foundingDate = $_POST['foundingDate'] ?? '';
                $founders = $_POST['founders'] ?? '';
                $milestones = $_POST['milestones'] ?? '';

                $sql = "UPDATE company_profile SET founding_date = ?, founders = ?, milestones = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $foundingDate, $founders, $milestones, $user_id);
                break;

            case 'mission_vision':
                $mission = $_POST['mission'] ?? '';
                $vision = $_POST['vision'] ?? '';

                $sql = "UPDATE company_profile SET mission = ?, vision = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $mission, $vision, $user_id);
                break;

            case 'products_services':
                $products = $_POST['products'] ?? '';
                $usp = $_POST['usp'] ?? '';

                $sql = "UPDATE company_profile SET products = ?, usp = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $products, $usp, $user_id);
                break;

            case 'achievements_awards':
                $awards = $_POST['awards'] ?? '';
                $testimonials = $_POST['testimonials'] ?? '';

                $sql = "UPDATE company_profile SET awards = ?, testimonials = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $awards, $testimonials, $user_id);
                break;

            case 'about_us':
                $aboutUs = $_POST['about_us'] ?? '';

                $sql = "UPDATE company_profile SET about_us = ? WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $aboutUs, $user_id);
                break;

            case 'save_all':
                // Simplify the save all functionality
                $companyName = $_POST['companyName'] ?? $profile['company_name'] ?? '';
                $tagline = $_POST['tagline'] ?? $profile['tagline'] ?? '';
                $location = $_POST['location'] ?? $profile['location'] ?? '';
                $contactInfo = $_POST['contactInfo'] ?? $profile['contact_info'] ?? '';
                $foundingDate = $_POST['foundingDate'] ?? $profile['founding_date'] ?? '';
                $founders = $_POST['founders'] ?? $profile['founders'] ?? '';
                $milestones = $_POST['milestones'] ?? $profile['milestones'] ?? '';
                $mission = $_POST['mission'] ?? $profile['mission'] ?? '';
                $vision = $_POST['vision'] ?? $profile['vision'] ?? '';
                $products = $_POST['products'] ?? $profile['products'] ?? '';
                $usp = $_POST['usp'] ?? $profile['usp'] ?? '';
                $awards = $_POST['awards'] ?? $profile['awards'] ?? '';
                $testimonials = $_POST['testimonials'] ?? $profile['testimonials'] ?? '';
                $aboutUs = $_POST['about_us'] ?? $profile['about_us'] ?? '';

                // Handle file uploads
                $logo = null;
                $officePhoto = null;
                $infographic = null;

                // Check if files were uploaded and process them
                if (!empty($_FILES['logoUpload']['tmp_name'])) {
                    $logo = file_get_contents($_FILES['logoUpload']['tmp_name']);
                }
                if (!empty($_FILES['officePhotoUpload']['tmp_name'])) {
                    $officePhoto = file_get_contents($_FILES['officePhotoUpload']['tmp_name']);
                }
                if (!empty($_FILES['infographicUpload']['tmp_name'])) {
                    $infographic = file_get_contents($_FILES['infographicUpload']['tmp_name']);
                }

                // First update text fields
                $sql = "UPDATE company_profile SET 
                        company_name = ?, 
                        tagline = ?, 
                        location = ?, 
                        contact_info = ?, 
                        founding_date = ?, 
                        founders = ?, 
                        milestones = ?, 
                        mission = ?, 
                        vision = ?, 
                        products = ?, 
                        usp = ?, 
                        awards = ?, 
                        testimonials = ?, 
                        about_us = ? 
                        WHERE user_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssssssi", 
                    $companyName, $tagline, $location, $contactInfo, 
                    $foundingDate, $founders, $milestones, 
                    $mission, $vision, $products, $usp, 
                    $awards, $testimonials, $aboutUs, $user_id);
                $stmt->execute();
                
                // Now update image fields if they were uploaded
                if ($logo !== null || $officePhoto !== null || $infographic !== null) {
                    $updateFields = [];
                    $bindTypes = "";
                    $bindParams = [];
                    
                    if ($logo !== null) {
                        $updateFields[] = "logo = ?";
                        $bindTypes .= "s";
                        $bindParams[] = $logo;
                    }
                    
                    if ($officePhoto !== null) {
                        $updateFields[] = "office_photo = ?";
                        $bindTypes .= "s";
                        $bindParams[] = $officePhoto;
                    }
                    
                    if ($infographic !== null) {
                        $updateFields[] = "infographic = ?";
                        $bindTypes .= "s";
                        $bindParams[] = $infographic;
                    }
                    
                    if (!empty($updateFields)) {
                        $sql = "UPDATE company_profile SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
                        $bindTypes .= "i";
                        $bindParams[] = $user_id;
                        
                        $stmt = $conn->prepare($sql);
                        
                        // Create the parameter array for bind_param
                        $bindParamsArray = array_merge([$bindTypes], $bindParams);
                        call_user_func_array([$stmt, 'bind_param'], $bindParamsArray);
                        
                        $stmt->execute();
                    }
                }
                break;

            default:
                throw new Exception("Invalid section.");
        }

        // Execute the query
        if ($stmt->execute()) {
            echo "<script>alert('$section updated successfully!');</script>";
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Politeknik Brunei</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .admin-panel {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .admin-panel h2 {
            color: #00447c;
            margin-top: 0;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        /* Tab navigation styling */
        .tab-nav {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            background: none;
            border: none;
            padding: 10px 15px;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 4px 4px 0 0;
            font-weight: bold;
            color: #777;
        }
        
        .tab-btn.active {
            background-color: #00447c;
            color: white;
        }
        
        /* Form sections */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .edit-form {
            background-color: #f9f9f9;
            border-radius: 6px;
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .edit-form h2 {
            font-size: 1.1rem;
            margin-top: 0;
            margin-bottom: 12px;
            color: #00447c;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 8px;
        }
        
        input[type="text"], 
        input[type="password"], 
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: inherit;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        input[type="file"] {
            margin-bottom: 12px;
            display: block;
        }
        
        button {
            background-color: #00447c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #003366;
        }
        
        #saveAllForm button {
            background-color: #006600;
            padding: 10px 15px;
            font-size: 1em;
            width: 100%;
            margin-bottom: 15px;
        }
        
        #saveAllForm button:hover {
            background-color: #005500;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        /* PB Theme Colors */
        .pb-header {
            background-color: #00447c;
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .pb-header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .pb-logo {
            height: 60px;
            margin-right: 15px;
        }
        
        .back-btn {
            background-color: #f0f4f8;
            color: #00447c;
            border: 2px solid #00447c;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background-color: #00447c;
            color: white;
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            /* Floating save button styles */
            .floating-save {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
            }
            
            .floating-save button {
                padding: 15px 20px;
                font-size: 16px;
                background-color: #006600;
                color: white;
                border: none;
                border-radius: 50px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                cursor: pointer;
                display: flex;
                align-items: center;
                transition: all 0.3s ease;
            }
            
            .floating-save button:hover {
                background-color: #005500;
                transform: translateY(-3px);
                box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            }
            
            .floating-save button i {
                margin-right: 8px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Politeknik Brunei Header -->
            <div class="pb-header">
                <div style="display: flex; align-items: center;">
                    <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" class="pb-logo" onerror="this.src='/Website/assets/images/default-logo.png'; this.onerror=null;">
                    <h1>Politeknik Brunei - Company Profile Editor</h1>
                </div>
                <a href="/Website/company_profile/company_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            
            <div class="admin-panel">
                <h2>Edit Company Profile</h2>
                
                <!-- Tab Navigation -->
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="visual">Visual Elements</button>
                    <button class="tab-btn" data-tab="overview">Company Overview</button>
                    <button class="tab-btn" data-tab="history">History & Background</button>
                    <button class="tab-btn" data-tab="mission">Mission & Vision</button>
                    <button class="tab-btn" data-tab="products">Products & Services</button>
                    <button class="tab-btn" data-tab="achievements">Achievements & Awards</button>
                    <button class="tab-btn" data-tab="about">About Us</button>
                </div>
                
                <!-- Tab content sections remain the same -->
                <!-- Visual Elements Tab -->
                <div id="visual-tab" class="tab-content active">
                    <div class="edit-form">
                        <h2>Visual Elements</h2>
                        <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="section" value="visual_elements">
                            
                            <label for="logoUpload">Company Logo:</label>
                            <?php if (!empty($profile['logo'])): ?>
                                <div class="image-preview">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['logo']); ?>" alt="Company Logo" style="max-width: 200px; margin-bottom: 10px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="logoUpload" id="logoUpload">
                            
                            <label for="officePhotoUpload">Office Photo:</label>
                            <?php if (!empty($profile['office_photo'])): ?>
                                <div class="image-preview">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['office_photo']); ?>" alt="Office Photo" style="max-width: 200px; margin-bottom: 10px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="officePhotoUpload" id="officePhotoUpload">
                            
                            <label for="infographicUpload">Company Infographic:</label>
                            <?php if (!empty($profile['infographic'])): ?>
                                <div class="image-preview">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['infographic']); ?>" alt="Company Infographic" style="max-width: 200px; margin-bottom: 10px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="infographicUpload" id="infographicUpload">
                            
                            <button type="submit">Save Visual Elements</button>
                        </form>
                    </div>
                </div>
                
                <!-- Company Overview Tab -->
                <div id="overview-tab" class="tab-content">
                    <!-- Content for Company Overview tab -->
                    <div class="edit-form">
                        <h2>Company Overview</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="company_overview">
                            
                            <label for="companyName">Company Name:</label>
                            <input type="text" name="companyName" id="companyName" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>">
                            
                            <label for="tagline">Tagline:</label>
                            <input type="text" name="tagline" id="tagline" value="<?php echo htmlspecialchars($profile['tagline'] ?? ''); ?>">
                            
                            <label for="location">Location:</label>
                            <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                            
                            <label for="contactInfo">Contact Information:</label>
                            <input type="text" name="contactInfo" id="contactInfo" value="<?php echo htmlspecialchars($profile['contact_info'] ?? ''); ?>">
                            
                            <button type="submit">Save Company Overview</button>
                        </form>
                    </div>
                </div>
                
                <!-- Other tabs content... -->
                
                <!-- History & Background Tab -->
                <div id="history-tab" class="tab-content">
                    <div class="edit-form">
                        <h2>History & Background</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="history_background">
                            
                            <label for="foundingDate">Founding Date:</label>
                            <input type="text" name="foundingDate" id="foundingDate" value="<?php echo htmlspecialchars($profile['founding_date'] ?? ''); ?>">
                            
                            <label for="founders">Founders:</label>
                            <input type="text" name="founders" id="founders" value="<?php echo htmlspecialchars($profile['founders'] ?? ''); ?>">
                            
                            <label for="milestones">Key Milestones:</label>
                            <textarea name="milestones" id="milestones"><?php echo htmlspecialchars($profile['milestones'] ?? ''); ?></textarea>
                            
                            <button type="submit">Save History & Background</button>
                        </form>
                    </div>
                </div>
                
                <!-- Mission & Vision Tab -->
                <div id="mission-tab" class="tab-content">
                    <div class="edit-form">
                        <h2>Mission & Vision</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="mission_vision">
                            
                            <label for="mission">Mission Statement:</label>
                            <textarea name="mission" id="mission"><?php echo htmlspecialchars($profile['mission'] ?? ''); ?></textarea>
                            
                            <label for="vision">Vision Statement:</label>
                            <textarea name="vision" id="vision"><?php echo htmlspecialchars($profile['vision'] ?? ''); ?></textarea>
                            
                            <button type="submit">Save Mission & Vision</button>
                        </form>
                    </div>
                </div>
                
                <!-- Products & Services Tab -->
                <div id="products-tab" class="tab-content">
                    <div class="edit-form">
                        <h2>Products & Services</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="products_services">
                            
                            <label for="products">Products & Services:</label>
                            <textarea name="products" id="products"><?php echo htmlspecialchars($profile['products'] ?? ''); ?></textarea>
                            
                            <label for="usp">Unique Selling Proposition:</label>
                            <input type="text" name="usp" id="usp" value="<?php echo htmlspecialchars($profile['usp'] ?? ''); ?>">
                            
                            <button type="submit">Save Products & Services</button>
                        </form>
                    </div>
                </div>
                
                <!-- Achievements & Awards Tab -->
                <div id="achievements-tab" class="tab-content">
                    <div class="edit-form">
                        <h2>Achievements & Awards</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="achievements_awards">
                            
                            <label for="awards">Awards & Recognition:</label>
                            <textarea name="awards" id="awards"><?php echo htmlspecialchars($profile['awards'] ?? ''); ?></textarea>
                            
                            <label for="testimonials">Client Testimonials:</label>
                            <textarea name="testimonials" id="testimonials"><?php echo htmlspecialchars($profile['testimonials'] ?? ''); ?></textarea>
                            
                            <button type="submit">Save Achievements & Awards</button>
                        </form>
                    </div>
                </div>
                
                <!-- About Us Tab -->
                <div id="about-tab" class="tab-content">
                    <div class="edit-form">
                        <h2>About Us</h2>
                        <form action="edit_profile.php" method="post">
                            <input type="hidden" name="section" value="about_us">
                            
                            <label for="about_us">About Us Content:</label>
                            <textarea name="about_us" id="about_us"><?php echo htmlspecialchars($profile['about_us'] ?? ''); ?></textarea>
                            
                            <button type="submit">Save About Us</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Save All Button -->
        <div class="floating-save">
            <form action="edit_profile.php" method="post" enctype="multipart/form-data" id="saveAllForm">
                <input type="hidden" name="section" value="save_all">
                
                <!-- Hidden inputs to store values from all tabs -->
                <input type="hidden" name="companyName" id="hiddenCompanyName" value="<?php echo htmlspecialchars($profile['company_name'] ?? ''); ?>">
                <input type="hidden" name="tagline" id="hiddenTagline" value="<?php echo htmlspecialchars($profile['tagline'] ?? ''); ?>">
                <input type="hidden" name="location" id="hiddenLocation" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
                <input type="hidden" name="contactInfo" id="hiddenContactInfo" value="<?php echo htmlspecialchars($profile['contact_info'] ?? ''); ?>">
                <input type="hidden" name="foundingDate" id="hiddenFoundingDate" value="<?php echo htmlspecialchars($profile['founding_date'] ?? ''); ?>">
                <input type="hidden" name="founders" id="hiddenFounders" value="<?php echo htmlspecialchars($profile['founders'] ?? ''); ?>">
                <input type="hidden" name="milestones" id="hiddenMilestones" value="<?php echo htmlspecialchars($profile['milestones'] ?? ''); ?>">
                <input type="hidden" name="mission" id="hiddenMission" value="<?php echo htmlspecialchars($profile['mission'] ?? ''); ?>">
                <input type="hidden" name="vision" id="hiddenVision" value="<?php echo htmlspecialchars($profile['vision'] ?? ''); ?>">
                <input type="hidden" name="products" id="hiddenProducts" value="<?php echo htmlspecialchars($profile['products'] ?? ''); ?>">
                <input type="hidden" name="usp" id="hiddenUsp" value="<?php echo htmlspecialchars($profile['usp'] ?? ''); ?>">
                <input type="hidden" name="awards" id="hiddenAwards" value="<?php echo htmlspecialchars($profile['awards'] ?? ''); ?>">
                <input type="hidden" name="testimonials" id="hiddenTestimonials" value="<?php echo htmlspecialchars($profile['testimonials'] ?? ''); ?>">
                <input type="hidden" name="about_us" id="hiddenAboutUs" value="<?php echo htmlspecialchars($profile['about_us'] ?? ''); ?>">
                
                <!-- Hidden file inputs for visual elements -->
                <input type="file" name="logoUpload" id="hiddenLogoUpload" style="display:none;">
                <input type="file" name="officePhotoUpload" id="hiddenOfficePhotoUpload" style="display:none;">
                <input type="file" name="infographicUpload" id="hiddenInfographicUpload" style="display:none;">
                
                <button type="submit"><i class="fas fa-save"></i> Save All Changes</button>
            </form>
        </div>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
    
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and content
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Show corresponding content
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
        
        // Save All functionality - Updated to collect all form values
        document.getElementById('saveAllForm').addEventListener('submit', function(event) {
            // Prevent default form submission
            event.preventDefault();
            
            // Create a FormData object
            const formData = new FormData(this);
            
            // Collect values from all visible form fields
            formData.set('companyName', document.querySelector('input[name="companyName"]')?.value || '');
            formData.set('tagline', document.querySelector('input[name="tagline"]')?.value || '');
            formData.set('location', document.querySelector('input[name="location"]')?.value || '');
            formData.set('contactInfo', document.querySelector('input[name="contactInfo"]')?.value || '');
            formData.set('foundingDate', document.querySelector('input[name="foundingDate"]')?.value || '');
            formData.set('founders', document.querySelector('input[name="founders"]')?.value || '');
            formData.set('milestones', document.querySelector('textarea[name="milestones"]')?.value || '');
            formData.set('mission', document.querySelector('textarea[name="mission"]')?.value || '');
            formData.set('vision', document.querySelector('textarea[name="vision"]')?.value || '');
            formData.set('products', document.querySelector('textarea[name="products"]')?.value || '');
            formData.set('usp', document.querySelector('input[name="usp"]')?.value || '');
            formData.set('awards', document.querySelector('textarea[name="awards"]')?.value || '');
            formData.set('testimonials', document.querySelector('textarea[name="testimonials"]')?.value || '');
            formData.set('about_us', document.querySelector('textarea[name="about_us"]')?.value || '');
            
            // Handle file uploads
            const logoInput = document.querySelector('#logoUpload');
            if (logoInput && logoInput.files.length > 0) {
                formData.set('logoUpload', logoInput.files[0]);
            }
            
            const officePhotoInput = document.querySelector('#officePhotoUpload');
            if (officePhotoInput && officePhotoInput.files.length > 0) {
                formData.set('officePhotoUpload', officePhotoInput.files[0]);
            }
            
            const infographicInput = document.querySelector('#infographicUpload');
            if (infographicInput && infographicInput.files.length > 0) {
                formData.set('infographicUpload', infographicInput.files[0]);
            }
            
            // Submit the form using fetch API
            fetch('edit_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    alert('All changes saved successfully!');
                    window.location.reload();
                } else {
                    alert('Error saving changes. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving changes.');
            });
        });
    </script>
</body>
</html>