<?php
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

// Fetch the latest profile data
$sql = "SELECT * FROM company_profile ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    $row = []; // Default empty data
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1, h2 {
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        header {
            background-color: #3498db;
            color: white;
            padding: 20px 0;
            text-align: center;
            border-bottom: 5px solid #2980b9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        header button {
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
            margin-left: 10px;
        }

        header button:hover {
            background-color: #34495e;
        }

        /* Visual Elements Section */
        .visual-elements {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .visual-elements img {
            max-width: 30%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Display Section */
        .display-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .display-section h2 {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .display-section p {
            margin: 10px 0;
        }

        .display-section ul {
            list-style-type: disc;
            margin-left: 20px;
        }

        /* Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
            border-top: 5px solid #3498db;
        }

        footer p {
            margin: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .visual-elements {
                flex-direction: column;
                align-items: flex-start;
            }

            .visual-elements img {
                max-width: 100%;
                margin-bottom: 20px;
            }

            header {
                flex-direction: column;
                text-align: center;
            }

            header h1 {
                margin-bottom: 10px;
            }

            header button {
                margin: 5px 0;
            }
        }
        
        /* Company Visuals Styles */
        .company-visuals {
            margin: 30px 0;
        }
        
        .visual-section {
            margin-bottom: 30px;
        }
        
        .visual-section h3 {
            color: #00447c;
            margin-bottom: 15px;
            font-size: 1.3rem;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }
        
        .image-container {
            text-align: center;
        }
        
        .profile-image {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation buttons with blue line -->
    <div style="background-color: #3498db; padding: 10px 0; border-bottom: 5px solid #2980b9; text-align: right; margin-bottom: 20px; position: relative;">
        <!-- Politeknik Logo positioned on the blue line -->
        <div style="position: absolute; top: 50%; left: 20px; transform: translateY(-50%);">
            <a href="/Website/index.php">
                <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 50px;">
            </a>
        </div>
        
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <a href="/Website/company_profile/company_dashboard.php" style="display: inline-block; padding: 8px 15px; background-color: #2c3e50; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Back to Dashboard</a>
            <a href="about_us.php" style="display: inline-block; padding: 8px 15px; background-color: #2c3e50; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">About Us</a>
            <a href="edit_profile.php" style="display: inline-block; padding: 8px 15px; background-color: #2c3e50; color: white; text-decoration: none; border-radius: 4px; margin-left: 10px;">Edit</a>
        </div>
    </div>

    <div class="container">
        <!-- Visual Elements Display -->
        <div class="visual-elements">
            <img id="displayLogo" src="data:image/png;base64,<?= base64_encode($row['logo']) ?>" alt="Company Logo">
        </div>

        <!-- Company Visuals Section -->
        <div class="company-visuals">
            <?php if (!empty($row['office_photo'])): ?>
            <div class="visual-section">
                <h3>Our Office</h3>
                <div class="image-container">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['office_photo']) ?>" alt="Our Office" class="profile-image">
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($row['infographic'])): ?>
            <div class="visual-section">
                <h3>Company Infographic</h3>
                <div class="image-container">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['infographic']) ?>" alt="Company Infographic" class="profile-image">
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Display Section -->
        <div class="display-section">
            <!-- Company Overview Display -->
            <div class="section">
                <h2>Company Overview</h2>
                <p><strong>Name:</strong> <span id="displayCompanyName"><?= $row['company_name'] ?? '[Company Name]' ?></span></p>
                <p><strong>Tagline/Slogan:</strong> <span id="displayTagline"><?= $row['tagline'] ?? '[Tagline/Slogan]' ?></span></p>
                <p><strong>Location:</strong> <span id="displayLocation"><?= $row['location'] ?? '[Location]' ?></span></p>
                <p><strong>Contact Information:</strong> <span id="displayContactInfo"><?= $row['contact_info'] ?? '[Contact Information]' ?></span></p>
            </div>

            <!-- History and Background Display -->
            <div class="section">
                <h2>History and Background</h2>
                <p><strong>Founding Date:</strong> <span id="displayFoundingDate"><?= $row['founding_date'] ?? '[Founding Date]' ?></span></p>
                <p><strong>Founders:</strong> <span id="displayFounders"><?= $row['founders'] ?? '[Founders]' ?></span></p>
                <p><strong>Milestones:</strong></p>
                <ul id="displayMilestones">
                    <?php
                    if (!empty($row['milestones'])) {
                        $milestones = explode("\n", $row['milestones']);
                        foreach ($milestones as $milestone) {
                            echo "<li>$milestone</li>";
                        }
                    } else {
                        echo "<li>[Milestone 1]</li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Mission and Vision Display -->
            <div class="section">
                <h2>Mission and Vision Statements</h2>
                <p><strong>Mission Statement:</strong> <span id="displayMission"><?= $row['mission'] ?? '[Mission Statement]' ?></span></p>
                <p><strong>Vision Statement:</strong> <span id="displayVision"><?= $row['vision'] ?? '[Vision Statement]' ?></span></p>
            </div>

            <!-- Products and Services Display -->
            <div class="section">
                <h2>Products and Services</h2>
                <p><strong>Product/Service Offerings:</strong></p>
                <ul id="displayProducts">
                    <?php
                    if (!empty($row['products'])) {
                        $products = explode("\n", $row['products']);
                        foreach ($products as $product) {
                            echo "<li>$product</li>";
                        }
                    } else {
                        echo "<li>[Product/Service 1]</li>";
                    }
                    ?>
                </ul>
                <p><strong>Unique Selling Proposition (USP):</strong> <span id="displayUsp"><?= $row['usp'] ?? '[USP]' ?></span></p>
            </div>

            <!-- Achievements and Awards Display -->
            <div class="section">
                <h2>Achievements and Awards</h2>
                <p><strong>Recognition:</strong></p>
                <ul id="displayAwards">
                    <?php
                    if (!empty($row['awards'])) {
                        $awards = explode("\n", $row['awards']);
                        foreach ($awards as $award) {
                            echo "<li>$award</li>";
                        }
                    } else {
                        echo "<li>[Award/Certification 1]</li>";
                    }
                    ?>
                </ul>
                <p><strong>Testimonials/Case Studies:</strong></p>
                <ul id="displayTestimonials">
                    <?php
                    if (!empty($row['testimonials'])) {
                        $testimonials = explode("\n", $row['testimonials']);
                        foreach ($testimonials as $testimonial) {
                            echo "<li>$testimonial</li>";
                        }
                    } else {
                        echo "<li>[Testimonial/Case Study 1]</li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Politeknik Brunei. All rights reserved.</p>
    </footer>
</body>
</html>