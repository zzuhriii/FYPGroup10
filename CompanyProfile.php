<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "company_profile";

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
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <h1>Company Profile</h1>
        <div>
            <a href="about_us.php"><button>About Us</button></a>
            <a href="edit_profile.php"><button>Edit</button></a>
        </div>
    </header>

    <div class="container">
        <!-- Visual Elements Display -->
        <div class="visual-elements">
            <img id="displayLogo" src="data:image/png;base64,<?= base64_encode($row['logo']) ?>" alt="Company Logo">
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
        <p>&copy; 2023 Your Company Name. All rights reserved.</p>
    </footer>
</body>
</html>