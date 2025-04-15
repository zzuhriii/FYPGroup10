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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        
        .page-title {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .location-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #4285f4;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3367d6;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .map-container {
            height: 400px;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        #map {
            height: 100%;
            width: 100%;
        }
        
        .map-instructions {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 0 0 4px 4px;
            position: relative;
        }
        
        .map-instructions-container {
            margin-bottom: 20px;
        }
        
        .toggle-instructions-btn {
            width: 100%;
            padding: 10px 15px;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: left;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toggle-instructions-btn:hover {
            background-color: #3367d6;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4285f4;
            text-decoration: none;
        }
        
        .back-link i {
            margin-right: 5px;
        }
    </style>
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
        
        <!-- Replace the Google Maps container and scripts with OpenStreetMap -->
        
        <!-- Keep the map container div -->
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
        
        <!-- Replace Google Maps JavaScript with OpenStreetMap -->
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        
        <script>
            let map;
            let marker;
            
            // Initialize the map when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                // Default coordinates (Brunei)
                const defaultLat = <?php echo $location['latitude'] ?? '4.8904'; ?>;
                const defaultLng = <?php echo $location['longitude'] ?? '114.9489'; ?>;
                
                // Initialize the map with Leaflet
                map = L.map('map').setView([defaultLat, defaultLng], 12);
                
                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                // Create a marker
                marker = L.marker([defaultLat, defaultLng], {
                    draggable: true
                }).addTo(map);
                
                // Update form fields when marker is dragged
                marker.on('dragend', function() {
                    const position = marker.getLatLng();
                    document.getElementById('latitude').value = position.lat;
                    document.getElementById('longitude').value = position.lng;
                    
                    // Get address from coordinates (reverse geocoding)
                    reverseGeocode(position.lat, position.lng);
                });
                
                // Add click event to map to place marker
                map.on('click', function(e) {
                    marker.setLatLng(e.latlng);
                    document.getElementById('latitude').value = e.latlng.lat;
                    document.getElementById('longitude').value = e.latlng.lng;
                    
                    // Get address from coordinates (reverse geocoding)
                    reverseGeocode(e.latlng.lat, e.latlng.lng);
                });
                
                // Add geocoder control for address search
                const geocoder = L.Control.geocoder({
                    defaultMarkGeocode: false
                }).addTo(map);
                
                // Handle geocoding results
                geocoder.on('markgeocode', function(e) {
                    const result = e.geocode;
                    
                    // Update marker position
                    marker.setLatLng(result.center);
                    
                    // Update form fields
                    document.getElementById('latitude').value = result.center.lat;
                    document.getElementById('longitude').value = result.center.lng;
                    document.getElementById('address').value = result.name;
                    
                    // Zoom to the location
                    map.fitBounds(result.bbox);
                });
                
                // Add event listener to address input for manual search
                document.getElementById('address').addEventListener('change', function() {
                    geocodeAddress(this.value);
                });
            });
            
            // Function to perform reverse geocoding
            function reverseGeocode(lat, lng) {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
                
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'PB Marketing Day Website'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('address').value = data.display_name;
                    }
                })
                .catch(error => {
                    console.error('Error during reverse geocoding:', error);
                });
            }
            
            // Function to geocode an address
            function geocodeAddress(address) {
                if (!address) return;
                
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
                
                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'PB Marketing Day Website'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        // Update marker position
                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], 15);
                        
                        // Update form fields
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                    }
                })
                .catch(error => {
                    console.error('Error during geocoding:', error);
                });
            }
            
            // Toggle instructions visibility
            document.getElementById('toggleInstructions').addEventListener('click', function() {
                const instructions = document.getElementById('mapInstructions');
                const button = document.getElementById('toggleInstructions');
                const icon = button.querySelector('.fa-chevron-down, .fa-chevron-up');
                
                if (instructions.style.display === 'none') {
                    instructions.style.display = 'block';
                    button.innerHTML = '<i class="fas fa-info-circle"></i> Hide Map Instructions <i class="fas fa-chevron-up"></i>';
                } else {
                    instructions.style.display = 'none';
                    button.innerHTML = '<i class="fas fa-info-circle"></i> Show Map Instructions <i class="fas fa-chevron-down"></i>';
                }
            });
            
            // Remove the old dismiss functions since we're using a toggle approach now
            // function dismissInstructions() {
            //     document.getElementById('mapInstructions').style.display = 'none';
            //     localStorage.setItem('mapInstructionsDismissed', 'true');
            // }
            
            // // Check if instructions were previously dismissed
            // if (localStorage.getItem('mapInstructionsDismissed') === 'true') {
            //     document.getElementById('mapInstructions').style.display = 'none';
            // }
        </script>
        
        <!-- Remove the Google Maps API script -->
        <!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places&callback=initMap" async defer></script> -->
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
</body>
</html>