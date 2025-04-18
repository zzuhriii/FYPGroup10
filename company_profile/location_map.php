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

// Get all company locations
$sql = "SELECT cl.*, u.name as company_name 
        FROM company_locations cl 
        JOIN users u ON cl.user_id = u.id 
        WHERE u.user_type = 'company'";
$result = $conn->query($sql);

$locations = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Function to calculate distance between two points using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Radius of the earth in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = 
        sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
        sin($dLon/2) * sin($dLon/2); 
    $c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
    $distance = $R * $c; // Distance in km
    return $distance;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Locations - Politeknik Brunei Marketing Day</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/location_map.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@2.0.0/Control.FullScreen.css">
</head>
<body>
    <!-- Politeknik Logo at top left -->
    <div style="position: absolute; top: 10px; left: 10px;">
        <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo" style="max-height: 60px;">
    </div>
    
    <div class="container">
        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="/Website/main/graduate_dashboard.php"><i class="fas fa-home"></i> Home</a>
            <a href="/Website/jobs/graduates_homepage.php"><i class="fas fa-briefcase"></i> Jobs</a>
            <a href="/Website/about.php"><i class="fas fa-info-circle"></i> About</a>
            <a href="/Website/contact.php"><i class="fas fa-envelope"></i> Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_type'] === 'student'): ?>
                    <a href="/Website/student_profile/student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <?php endif; ?>
                <a href="/Website/authentication/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="/Website/authentication/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/Website/authentication/register.php"><i class="fas fa-user-plus"></i> Register</a>
            <?php endif; ?>
        </div>
        
        <h1 class="page-title">Find Companies Near You</h1>

        <div class="graduate-info-box" id="graduateInfoBox">
            <button onclick="closeInfoBox()">
                <i class="fas fa-times"></i>
            </button>
            <h3><i class="fas fa-info-circle"></i> For Graduates</h3>
            <p>Use this map to find potential employers in your area. You can:</p>
            <ul>
                <li>Search for companies by name or filter by industry</li>
                <li>Use the "Near Me" feature to find companies close to your location</li>
                <li>Click on any company card to view their location on the map</li>
                <li>Get directions to company offices for interviews or visits</li>
            </ul>
        </div>

        <div class="search-filter-container">
            <div style="flex: 1;">
                <input type="text" id="companySearch" placeholder="Search companies by name..." class="search-input">
            </div>
            <div>
                <select id="industryFilter" class="filter-select">
                    <option value="">All Industries</option>
                    <option value="Technology">Technology</option>
                    <option value="Finance">Finance</option>
                    <option value="Healthcare">Healthcare</option>
                    <option value="Education">Education</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Retail">Retail</option>
                    <option value="Services">Services</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <select id="jobTypeFilter" class="filter-select">
                    <option value="">All Job Types</option>
                    <option value="Full-time">Full-time</option>
                    <option value="Part-time">Part-time</option>
                    <option value="Internship">Internship</option>
                    <option value="Graduate Program">Graduate Program</option>
                    <option value="Contract">Contract</option>
                </select>
            </div>
        </div>
        
        <div class="location-controls">
            <button id="nearMeBtn" class="location-btn">
                <i class="fas fa-location-arrow"></i> Show Companies Near Me
            </button>
            <div id="nearMeStatus" style="display: none; margin-left: 10px;">
                <i class="fas fa-spinner fa-spin"></i> Finding your location...
            </div>
            <select id="distanceFilter" style="display: none; margin-left: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="5">Within 5 km</option>
                <option value="10" selected>Within 10 km</option>
                <option value="25">Within 25 km</option>
                <option value="50">Within 50 km</option>
                <option value="100">Within 100 km</option>
            </select>
        </div>
        
        <div class="map-container">
            <div id="map"></div>
        </div>
        
        <h2>All Companies</h2>
        <div class="company-list">
            <?php foreach ($locations as $location): ?>
                <div class="company-card" 
                     onclick="focusLocation(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>, '<?php echo htmlspecialchars(addslashes($location['company_name'])); ?>')"
                     data-lat="<?php echo $location['latitude']; ?>"
                     data-lng="<?php echo $location['longitude']; ?>"
                     data-industry="<?php echo htmlspecialchars($location['industry'] ?? 'Other'); ?>"
                     data-userid="<?php echo $location['user_id']; ?>">
                    <div class="company-name"><?php echo htmlspecialchars($location['company_name']); ?></div>
                    <?php if (!empty($location['industry'])): ?>
                        <div class="company-industry">
                            <i class="fas fa-industry"></i> <?php echo htmlspecialchars($location['industry']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="company-address">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($location['address']); ?>
                    </div>
                    <?php if (!empty($location['description'])): ?>
                        <div class="company-description"><?php echo htmlspecialchars($location['description']); ?></div>
                    <?php endif; ?>
                    <a href="/Website/company_profile/companyprofile.php?id=<?php echo $location['user_id']; ?>" class="view-profile-btn">
                        <i class="fas fa-building"></i> View Company Profile
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>" 
                       target="_blank" class="directions-btn">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($locations)): ?>
                <p>No company locations available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@2.0.0/Control.FullScreen.js"></script>
    
    <!-- Add this script to initialize the map with company locations -->
    <script>
        // Pass PHP data to JavaScript
        const locations = [
            <?php foreach ($locations as $location): ?>
            {
                lat: <?php echo $location['latitude']; ?>,
                lng: <?php echo $location['longitude']; ?>,
                name: "<?php echo htmlspecialchars(addslashes($location['company_name'])); ?>",
                address: "<?php echo htmlspecialchars(addslashes($location['address'])); ?>",
                description: "<?php echo htmlspecialchars(addslashes($location['description'] ?? '')); ?>",
                userId: <?php echo $location['user_id']; ?>,
                industry: "<?php echo htmlspecialchars(addslashes($location['industry'] ?? 'Other')); ?>"
            },
            <?php endforeach; ?>
        ];
        
        // Initialize markers when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Add markers for each company location
            locations.forEach(location => {
                addMarker(
                    location.lat,
                    location.lng,
                    location.name,
                    location.address,
                    location.description,
                    location.userId,
                    location.industry
                );
            });
        });
    </script>
    
    <!-- Include the external JavaScript file -->
    <script src="/Website/assets/js/location_map.js"></script>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/Website/footer.php'; ?>
</body>
</html>

<script>
    function closeInfoBox() {
        document.getElementById('graduateInfoBox').style.display = 'none';
        // Store in localStorage so it stays closed on future visits
        localStorage.setItem('graduateInfoBoxClosed', 'true');
    }
    
    // Check if the info box should be hidden on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (localStorage.getItem('graduateInfoBoxClosed') === 'true') {
            document.getElementById('graduateInfoBox').style.display = 'none';
        }
    });
</script>