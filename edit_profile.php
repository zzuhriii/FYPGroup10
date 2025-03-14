<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

                $sql = "UPDATE company_profile SET logo = ?, office_photo = ?, infographic = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $logo, $officePhoto, $infographic);
                break;

            case 'company_overview':
                $companyName = $_POST['companyName'] ?? '';
                $tagline = $_POST['tagline'] ?? '';
                $location = $_POST['location'] ?? '';
                $contactInfo = $_POST['contactInfo'] ?? '';

                $sql = "UPDATE company_profile SET company_name = ?, tagline = ?, location = ?, contact_info = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $companyName, $tagline, $location, $contactInfo);
                break;

            case 'history_background':
                $foundingDate = $_POST['foundingDate'] ?? '';
                $founders = $_POST['founders'] ?? '';
                $milestones = $_POST['milestones'] ?? '';

                $sql = "UPDATE company_profile SET founding_date = ?, founders = ?, milestones = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $foundingDate, $founders, $milestones);
                break;

            case 'mission_vision':
                $mission = $_POST['mission'] ?? '';
                $vision = $_POST['vision'] ?? '';

                $sql = "UPDATE company_profile SET mission = ?, vision = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $mission, $vision);
                break;

            case 'products_services':
                $products = $_POST['products'] ?? '';
                $usp = $_POST['usp'] ?? '';

                $sql = "UPDATE company_profile SET products = ?, usp = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $products, $usp);
                break;

            case 'achievements_awards':
                $awards = $_POST['awards'] ?? '';
                $testimonials = $_POST['testimonials'] ?? '';

                $sql = "UPDATE company_profile SET awards = ?, testimonials = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $awards, $testimonials);
                break;

            case 'about_us':
                $aboutUs = $_POST['about_us'] ?? '';

                $sql = "UPDATE company_profile SET about_us = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $aboutUs);
                break;

            case 'save_all':
                // Handle saving all sections at once
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

                $companyName = $_POST['companyName'] ?? '';
                $tagline = $_POST['tagline'] ?? '';
                $location = $_POST['location'] ?? '';
                $contactInfo = $_POST['contactInfo'] ?? '';
                $foundingDate = $_POST['foundingDate'] ?? '';
                $founders = $_POST['founders'] ?? '';
                $milestones = $_POST['milestones'] ?? '';
                $mission = $_POST['mission'] ?? '';
                $vision = $_POST['vision'] ?? '';
                $products = $_POST['products'] ?? '';
                $usp = $_POST['usp'] ?? '';
                $awards = $_POST['awards'] ?? '';
                $testimonials = $_POST['testimonials'] ?? '';
                $aboutUs = $_POST['about_us'] ?? '';

                $sql = "UPDATE company_profile SET 
                        logo = ?, 
                        office_photo = ?, 
                        infographic = ?, 
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
                        about_us = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssssssss", $logo, $officePhoto, $infographic, $companyName, $tagline, $location, $contactInfo, $foundingDate, $founders, $milestones, $mission, $vision, $products, $usp, $awards, $testimonials, $aboutUs);
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
    <title>Edit Profile</title>
    <style>
        /* Your existing styles */
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <h1>Edit Profile</h1>
        <div>
            <a href="CompanyProfile.php"><button>Back to Profile</button></a>
        </div>
    </header>

    <div class="container">
        <!-- Admin Panel -->
        <div class="admin-panel">
            <h2>Admin Panel</h2>
            <input type="password" id="adminPassword" placeholder="Enter Admin Password">
            <button onclick="loginAdmin()">Login</button>

            <!-- Edit Forms (Hidden until login) -->
            <div id="editForms" style="display: none;">
                <!-- Save All Button -->
                <form action="edit_profile.php" method="post" enctype="multipart/form-data" id="saveAllForm">
                    <input type="hidden" name="section" value="save_all">
                    <button type="submit" style="margin-bottom: 20px;">Save All</button>
                </form>

                <!-- Visual Elements Form -->
                <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="section" value="visual_elements">
                    <div class="edit-form">
                        <h2>Edit Visual Elements</h2>
                        <input type="file" name="logoUpload" accept="image/png">
                        <input type="file" name="officePhotoUpload" accept="image/png">
                        <input type="file" name="infographicUpload" accept="image/png">
                        <button type="submit">Save Visual Elements</button>
                    </div>
                </form>

                <!-- Company Overview Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="company_overview">
                    <div class="edit-form">
                        <h2>Edit Company Overview</h2>
                        <input type="text" name="companyName" placeholder="Company Name">
                        <input type="text" name="tagline" placeholder="Tagline/Slogan">
                        <input type="text" name="location" placeholder="Location">
                        <input type="text" name="contactInfo" placeholder="Contact Information">
                        <button type="submit">Save Company Overview</button>
                    </div>
                </form>

                <!-- History and Background Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="history_background">
                    <div class="edit-form">
                        <h2>Edit History and Background</h2>
                        <input type="text" name="foundingDate" placeholder="Founding Date">
                        <input type="text" name="founders" placeholder="Founders">
                        <textarea name="milestones" placeholder="Milestones (one per line)"></textarea>
                        <button type="submit">Save History and Background</button>
                    </div>
                </form>

                <!-- Mission and Vision Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="mission_vision">
                    <div class="edit-form">
                        <h2>Edit Mission and Vision</h2>
                        <textarea name="mission" placeholder="Mission Statement"></textarea>
                        <textarea name="vision" placeholder="Vision Statement"></textarea>
                        <button type="submit">Save Mission and Vision</button>
                    </div>
                </form>

                <!-- Products and Services Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="products_services">
                    <div class="edit-form">
                        <h2>Edit Products and Services</h2>
                        <textarea name="products" placeholder="Products/Services (one per line)"></textarea>
                        <input type="text" name="usp" placeholder="Unique Selling Proposition (USP)">
                        <button type="submit">Save Products and Services</button>
                    </div>
                </form>

                <!-- Achievements and Awards Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="achievements_awards">
                    <div class="edit-form">
                        <h2>Edit Achievements and Awards</h2>
                        <textarea name="awards" placeholder="Awards/Certifications (one per line)"></textarea>
                        <textarea name="testimonials" placeholder="Testimonials/Case Studies (one per line)"></textarea>
                        <button type="submit">Save Achievements and Awards</button>
                    </div>
                </form>

                <!-- About Us Form -->
                <form action="edit_profile.php" method="post">
                    <input type="hidden" name="section" value="about_us">
                    <div class="edit-form">
                        <h2>Edit About Us</h2>
                        <textarea name="about_us" placeholder="About Us Description"></textarea>
                        <button type="submit">Save About Us</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2023 Your Company Name. All rights reserved.</p>
    </footer>

    <script>
        // Admin Password (for demonstration purposes only)
        const ADMIN_PASSWORD = "admin123";

        // Function to login to admin panel
        function loginAdmin() {
            const password = document.getElementById('adminPassword').value;
            if (password === ADMIN_PASSWORD) {
                document.getElementById('editForms').style.display = 'block';
                alert('Login successful!');
            } else {
                alert('Incorrect password. Access denied.');
            }
        }

        // Function to handle Save All
        document.getElementById('saveAllForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData();

            // Collect data from all forms
            const forms = document.querySelectorAll('#editForms form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'file') {
                        if (input.files.length > 0) {
                            formData.append(input.name, input.files[0]);
                        }
                    } else {
                        formData.append(input.name, input.value);
                    }
                });
            });

            // Append the section value
            formData.append('section', 'save_all');

            // Submit the form data
            fetch('edit_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert('All sections updated successfully!');
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        });
    </script>
</body>
</html>