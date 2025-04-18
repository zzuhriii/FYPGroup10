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
$success_message = '';
$error_message = '';

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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location_name = $_POST['location_name'];
    $address = $_POST['address'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $description = $_POST['description'];
    
    // Check if location already exists for this company
    $check_sql = "SELECT id FROM company_locations WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing location
        $location = $check_result->fetch_assoc();
        $update_sql = "UPDATE company_locations SET location_name = ?, address = ?, latitude = ?, longitude = ?, description = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssddsi", $location_name, $address, $latitude, $longitude, $description, $location['id']);
        
        if ($update_stmt->execute()) {
            $success_message = "Location updated successfully!";
        } else {
            $error_message = "Error updating location: " . $conn->error;
        }
    } else {
        // Insert new location
        $insert_sql = "INSERT INTO company_locations (user_id, location_name, address, latitude, longitude, description) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("issdds", $user_id, $location_name, $address, $latitude, $longitude, $description);
        
        if ($insert_stmt->execute()) {
            $success_message = "Location added successfully!";
        } else {
            $error_message = "Error adding location: " . $conn->error;
        }
    }
}

// Get existing location data if available
$location_sql = "SELECT * FROM company_locations WHERE user_id = ?";
$location_stmt = $conn->prepare($location_sql);
$location_stmt->bind_param("i", $user_id);
$location_stmt->execute();
$location_result = $location_stmt->get_result();
$location = $location_result->fetch_assoc();

// Get company information
$company_sql = "SELECT * FROM users WHERE id = ? AND user_type = 'company'";
$company_stmt = $conn->prepare($company_sql);
$company_stmt->bind_param("i", $user_id);
$company_stmt->execute();
$company_result = $company_stmt->get_result();
$company = $company_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Location - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/manage_location.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>
    
    <div class="container">
        <a href="company_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        
        <h1 class="page-title">Manage Company Location</h1>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="map-instructions-container">
            <button id="toggleInstructions" class="toggle-instructions-btn">
                <i class="fas fa-info-circle"></i> Show Map Instructions <i class="fas fa-chevron-down"></i>
            </button>
            <div id="mapInstructions" class="map-instructions" style="display: none;">
                <p><strong>Instructions:</strong> Use the map below to set your company's location. You can either:</p>
                <ol>
                    <li>Search for your address in the search box</li>
                    <li>Click directly on the map to place a marker</li>
                    <li>Drag the marker to adjust the exact position</li>
                </ol>
                <p>The latitude and longitude fields will be automatically filled based on the marker position.</p>
            </div>
        </div>
        
        <div class="map-container">
            <div id="map"></div>
        </div>
        
        <form class="location-form" method="POST" action="">
            <div class="form-group">
                <label for="location_name">Location Name</label>
                <input type="text" id="location_name" name="location_name" class="form-control" value="<?php echo htmlspecialchars($location['location_name'] ?? $company['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control" value="<?php echo htmlspecialchars($location['address'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description (Optional)</label>
                <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($location['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" class="form-control" value="<?php echo htmlspecialchars($location['latitude'] ?? '4.8904'); ?>" required readonly>
                </div>
                <div style="flex: 1;">
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" class="form-control" value="<?php echo htmlspecialchars($location['longitude'] ?? '114.9489'); ?>" required readonly>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Location</button>
        </form>
        
        <!-- JavaScript libraries -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        
        <!-- Include the external JavaScript file -->
        <script src="/Website/assets/js/manage_location.js"></script>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
</body>
</html>