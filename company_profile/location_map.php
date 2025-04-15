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

// Remove the custom deg2rad function since PHP already has it built-in
// function deg2rad($deg) {
//     return $deg * (M_PI/180);
// }

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Locations - Politeknik Brunei Marketing Day</title>
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
        
        .page-title {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .map-container {
            height: 600px;
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        #map {
            height: 100%;
            width: 100%;
        }
        
        .company-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .company-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .company-card:hover {
            transform: translateY(-5px);
        }
        
        .company-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #4285f4;
        }
        
        .company-address {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .company-industry {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            background-color: #f0f0f0;
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .company-description {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .view-profile-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .view-profile-btn:hover {
            background-color: #3367d6;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            padding: 15px 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav-links a {
            margin: 0 15px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #4285f4;
        }
        
        .nav-links a i {
            margin-right: 5px;
        }
        
        .location-controls {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .location-btn {
            padding: 10px 15px;
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .location-btn:hover {
            background-color: #3367d6;
        }
        
        .nearby-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #4caf50;
            color: white;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
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

        <div class="graduate-info-box" id="graduateInfoBox" style="background-color: #f8f9fa; border-left: 4px solid #4285f4; padding: 15px; margin-bottom: 20px; border-radius: 4px; position: relative;">
            <button onclick="closeInfoBox()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer; font-size: 16px; color: #666;">
                <i class="fas fa-times"></i>
            </button>
            <h3 style="margin-top: 0; color: #4285f4;"><i class="fas fa-info-circle"></i> For Graduates</h3>
            <p>Use this map to find potential employers in your area. You can:</p>
            <ul style="margin-bottom: 0;">
                <li>Search for companies by name or filter by industry</li>
                <li>Use the "Near Me" feature to find companies close to your location</li>
                <li>Click on any company card to view their location on the map</li>
                <li>Get directions to company offices for interviews or visits</li>
            </ul>
        </div>

        <div class="search-filter-container" style="display: flex; gap: 15px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <input type="text" id="companySearch" placeholder="Search companies by name..." class="search-input" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            </div>
            <div>
                <select id="industryFilter" class="filter-select" style="padding: 10px; border-radius: 4px; border: 1px solid #ddd; min-width: 200px;">
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
                <select id="jobTypeFilter" class="filter-select" style="padding: 10px; border-radius: 4px; border: 1px solid #ddd; min-width: 200px;">
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
                     onclick="focusLocation(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>)"
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
                       target="_blank" class="directions-btn" style="display: inline-block; padding: 8px 15px; background-color: #34a853; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; margin-left: 10px;">
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
    <!-- Add Leaflet plugins -->
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@2.0.0/Control.FullScreen.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.fullscreen@2.0.0/Control.FullScreen.css">
    
    <script>
        let map;
        let markers = [];
        let markerClusterGroup;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map with improved options
            map = L.map('map', {
                fullscreenControl: true,
                zoomControl: true,
                scrollWheelZoom: true
            }).setView([4.8904, 114.9489], 10); // Center on Brunei
            
            // Add OpenStreetMap tile layer with higher quality
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Add scale control
            L.control.scale().addTo(map);
            
            // Add geocoder search control
            L.Control.geocoder({
                defaultMarkGeocode: false,
                position: 'topleft',
                placeholder: 'Search for a location...',
                errorMessage: 'Nothing found.'
            }).on('markgeocode', function(e) {
                const bbox = e.geocode.bbox;
                const poly = L.polygon([
                    bbox.getSouthEast(),
                    bbox.getNorthEast(),
                    bbox.getNorthWest(),
                    bbox.getSouthWest()
                ]).addTo(map);
                map.fitBounds(poly.getBounds());
                poly.openPopup();
                setTimeout(() => {
                    map.removeLayer(poly);
                }, 3000);
            }).addTo(map);
            
            // Initialize marker cluster group
            markerClusterGroup = L.markerClusterGroup({
                showCoverageOnHover: false,
                maxClusterRadius: 50,
                spiderfyOnMaxZoom: true
            });
            
            // Add markers for each company location with enhanced icons
            <?php foreach ($locations as $location): ?>
                addMarker(
                    <?php echo $location['latitude']; ?>, 
                    <?php echo $location['longitude']; ?>, 
                    "<?php echo htmlspecialchars($location['company_name']); ?>",
                    "<?php echo htmlspecialchars($location['address']); ?>",
                    "<?php echo htmlspecialchars($location['description'] ?? ''); ?>",
                    <?php echo $location['user_id']; ?>,
                    "<?php echo htmlspecialchars($location['industry'] ?? 'Other'); ?>"
                );
            <?php endforeach; ?>
            
            // Add the marker cluster group to the map
            map.addLayer(markerClusterGroup);
            
            // If we have locations, fit the map to show all markers
            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        });
        
        function addMarker(lat, lng, name, address, description, userId, industry) {
            // Create custom icon based on industry
            let iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
            
            // Assign different colors based on industry
            if (industry === 'Technology') {
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png';
            } else if (industry === 'Finance') {
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-gold.png';
            } else if (industry === 'Healthcare') {
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png';
            } else if (industry === 'Education') {
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png';
            } else if (industry === 'Manufacturing') {
                iconUrl = 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png';
            }
            
            const customIcon = L.icon({
                iconUrl: iconUrl,
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            const marker = L.marker([lat, lng], {
                title: name,
                icon: customIcon
            });
            
            // Create enhanced popup content
            let popupContent = `
                <div style="max-width: 300px; padding: 10px;">
                    <h3 style="margin-top: 0; color: #4285f4; border-bottom: 1px solid #eee; padding-bottom: 8px;">${name}</h3>
                    <p style="margin-bottom: 8px;"><i class="fas fa-map-marker-alt" style="color: #4285f4; margin-right: 5px;"></i>${address}</p>
            `;
            
            if (industry) {
                popupContent += `<p style="margin-bottom: 8px;"><i class="fas fa-industry" style="color: #4285f4; margin-right: 5px;"></i>${industry}</p>`;
            }
            
            if (description) {
                popupContent += `<p style="margin-bottom: 15px; border-left: 3px solid #eee; padding-left: 10px; font-style: italic;">${description}</p>`;
            }
            
            popupContent += `
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <a href="/Website/company_profile/companyprofile.php?id=${userId}" 
                           style="flex: 1; text-align: center; background-color: #4285f4; color: white; padding: 8px 0; border-radius: 4px; text-decoration: none; font-size: 14px;">
                           <i class="fas fa-building"></i> Company Profile
                        </a>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" 
                           target="_blank" style="flex: 1; text-align: center; background-color: #34a853; color: white; padding: 8px 0; border-radius: 4px; text-decoration: none; font-size: 14px;">
                           <i class="fas fa-directions"></i> Directions
                        </a>
                    </div>
                    <div style="margin-top: 10px;">
                        <a href="https://www.openstreetmap.org/directions?from=&to=${lat}%2C${lng}" 
                           target="_blank" style="display: block; text-align: center; background-color: #f8f9fa; color: #333; padding: 8px 0; border-radius: 4px; text-decoration: none; font-size: 14px; border: 1px solid #ddd;">
                           <i class="fas fa-route"></i> OpenStreetMap Directions
                        </a>
                    </div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markers.push(marker);
            markerClusterGroup.addLayer(marker);
            
            // Add a circle to highlight the area when marker is clicked
            marker.on('click', function() {
                // Remove any existing circles
                map.eachLayer(function(layer) {
                    if (layer instanceof L.Circle) {
                        map.removeLayer(layer);
                    }
                });
                
                // Add a new circle
                const circle = L.circle([lat, lng], {
                    color: '#4285f4',
                    fillColor: '#4285f4',
                    fillOpacity: 0.1,
                    radius: 500
                }).addTo(map);
                
                // Center map on marker with animation
                map.flyTo([lat, lng], 15, {
                    animate: true,
                    duration: 1
                });
            });
        }
        
        function focusLocation(lat, lng) {
            map.flyTo([lat, lng], 15, {
                animate: true,
                duration: 1
            });
            
            // Remove any existing circles
            map.eachLayer(function(layer) {
                if (layer instanceof L.Circle) {
                    map.removeLayer(layer);
                }
            });
            
            // Add a circle to highlight the area
            const circle = L.circle([lat, lng], {
                color: '#4285f4',
                fillColor: '#4285f4',
                fillOpacity: 0.1,
                radius: 500
            }).addTo(map);
            
            // Find and open the marker popup
            markers.forEach(marker => {
                const markerLatLng = marker.getLatLng();
                if (markerLatLng.lat === lat && markerLatLng.lng === lng) {
                    marker.openPopup();
                }
            });
        }
        
        // Near Me functionality
        document.getElementById('nearMeBtn').addEventListener('click', function() {
            const statusEl = document.getElementById('nearMeStatus');
            const distanceFilter = document.getElementById('distanceFilter');
            
            statusEl.style.display = 'inline-block';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        // Success - got location
                        const userLat = position.coords.latitude;
                        const userLng = position.coords.longitude;
                        
                        // Add user marker
                        const userMarker = L.marker([userLat, userLng], {
                            icon: L.divIcon({
                                className: 'user-location-marker',
                                html: '<div style="background-color: #4285f4; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white;"></div>',
                                iconSize: [22, 22],
                                iconAnchor: [11, 11]
                            })
                        }).addTo(map);
                        userMarker.bindPopup("<b>Your Location</b>").openPopup();
                        
                        // Create a circle around user location
                        const radiusCircle = L.circle([userLat, userLng], {
                            color: '#4285f4',
                            fillColor: '#4285f4',
                            fillOpacity: 0.1,
                            radius: 10000 // 10km default
                        }).addTo(map);
                        
                        // Center map on user location
                        map.setView([userLat, userLng], 12);
                        
                        // Calculate distances and update UI
                        updateNearbyCompanies(userLat, userLng, 10);
                        
                        // Show distance filter
                        distanceFilter.style.display = 'inline-block';
                        
                        // Update when distance filter changes
                        distanceFilter.addEventListener('change', function() {
                            const radius = parseInt(this.value);
                            radiusCircle.setRadius(radius * 1000);
                            updateNearbyCompanies(userLat, userLng, radius);
                        });
                        
                        // Update status
                        statusEl.innerHTML = '<i class="fas fa-check-circle" style="color: green;"></i> Location found!';
                        setTimeout(() => {
                            statusEl.style.display = 'none';
                        }, 3000);
                    },
                    function(error) {
                        // Error getting location
                        console.error("Error getting location:", error);
                        statusEl.innerHTML = '<i class="fas fa-exclamation-circle" style="color: red;"></i> Could not get your location';
                        setTimeout(() => {
                            statusEl.style.display = 'none';
                        }, 3000);
                    }
                );
            } else {
                statusEl.innerHTML = '<i class="fas fa-exclamation-circle" style="color: red;"></i> Geolocation not supported by your browser';
                setTimeout(() => {
                    statusEl.style.display = 'none';
                }, 3000);
            }
        });
        
        function updateNearbyCompanies(userLat, userLng, radius) {
            // Get all company cards
            const companyCards = document.querySelectorAll('.company-card');
            
            // Loop through each company
            companyCards.forEach(card => {
                // Remove any existing nearby badges
                const existingBadge = card.querySelector('.nearby-badge');
                if (existingBadge) {
                    existingBadge.remove();
                }
                
                // Get company coordinates
                const lat = parseFloat(card.getAttribute('data-lat'));
                const lng = parseFloat(card.getAttribute('data-lng'));
                
                // Calculate distance
                const distance = calculateDistance(userLat, userLng, lat, lng);
                
                // Update UI based on distance
                if (distance <= radius) {
                    // Add nearby badge
                    const companyName = card.querySelector('.company-name');
                    const badge = document.createElement('span');
                    badge.className = 'nearby-badge';
                    badge.innerHTML = `${distance.toFixed(1)} km away`;
                    companyName.appendChild(badge);
                    
                    // Highlight card
                    card.style.borderLeft = '4px solid #4caf50';
                } else {
                    // Reset card styling
                    card.style.borderLeft = 'none';
                }
            });
        }
        
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);
            const dLon = deg2rad(lon2 - lon1);
            const a = 
                Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon/2) * Math.sin(dLon/2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
            const distance = R * c; // Distance in km
            return distance;
        }
        
        function deg2rad(deg) {
            return deg * (Math.PI/180);
        }
        
        // Search and filter functionality
        document.getElementById('companySearch').addEventListener('input', filterCompanies);
        document.getElementById('industryFilter').addEventListener('change', filterCompanies);
        document.getElementById('jobTypeFilter').addEventListener('change', filterCompanies);
        
        function filterCompanies() {
            const searchTerm = document.getElementById('companySearch').value.toLowerCase();
            const industryFilter = document.getElementById('industryFilter').value;
            const jobTypeFilter = document.getElementById('jobTypeFilter').value;
            const companyCards = document.querySelectorAll('.company-card');
            
            // Clear all markers from map
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            // Filter companies
            let visibleCompanies = [];
            
            companyCards.forEach(card => {
                const companyName = card.querySelector('.company-name').textContent.toLowerCase();
                const companyIndustry = card.getAttribute('data-industry') || '';
                const jobTypes = card.getAttribute('data-job-types') || '';
                const lat = parseFloat(card.getAttribute('data-lat'));
                const lng = parseFloat(card.getAttribute('data-lng'));
                const userId = card.getAttribute('data-userid');
                const address = card.querySelector('.company-address').textContent;
                const descriptionEl = card.querySelector('.company-description');
                const description = descriptionEl ? descriptionEl.textContent : '';
                
                const matchesSearch = searchTerm === '' || companyName.includes(searchTerm);
                const matchesIndustry = industryFilter === '' || companyIndustry === industryFilter;
                const matchesJobType = jobTypeFilter === '' || jobTypes.includes(jobTypeFilter);
                
                if (matchesSearch && matchesIndustry && matchesJobType) {
                    card.style.display = 'block';
                    
                    // Add marker back to map
                    addMarker(lat, lng, card.querySelector('.company-name').textContent, 
                             address, description, userId);
                    
                    visibleCompanies.push({lat, lng});
                } else {
                    card.style.display = 'none';
                }
            });
            
            // If we have visible companies, fit the map to show all markers
            if (visibleCompanies.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    </script>
    
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