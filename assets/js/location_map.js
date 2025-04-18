// Initialize the map
let map = L.map('map', {
    fullscreenControl: true,
    fullscreenControlOptions: {
        position: 'topleft'
    }
}).setView([4.8903, 114.9422], 10); // Center on Brunei

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Create a marker cluster group
let markers = L.markerClusterGroup();
let markersList = [];

// Add a marker to the map
function addMarker(lat, lng, name, address, description, userId, industry) {
    const marker = L.marker([lat, lng]);
    
    // Create popup content
    let popupContent = `
        <div class="marker-popup">
            <h3>${name}</h3>
            <p><strong>Address:</strong> ${address}</p>
            ${description ? `<p>${description}</p>` : ''}
            <p><strong>Industry:</strong> ${industry || 'Not specified'}</p>
            <div class="popup-links">
                <a href="/Website/company_profile/companyprofile.php?id=${userId}" class="popup-link">
                    <i class="fas fa-building"></i> View Profile
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}" target="_blank" class="popup-link">
                    <i class="fas fa-directions"></i> Get Directions
                </a>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent);
    markers.addLayer(marker);
    
    // Store marker reference for later use
    markersList.push({
        marker: marker,
        lat: lat,
        lng: lng,
        name: name,
        industry: industry,
        userId: userId
    });
    
    return marker;
}

// Add the marker cluster to the map
map.addLayer(markers);

// Function to focus on a specific location
function focusLocation(lat, lng, name) {
    map.setView([lat, lng], 16); // Zoom level 16 for detailed view
    
    // Find and open the popup for this location
    markersList.forEach(item => {
        if (item.lat === lat && item.lng === lng) {
            item.marker.openPopup();
        }
    });
}

// Search functionality
document.getElementById('companySearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    filterCompanies(searchTerm, getIndustryFilter(), getJobTypeFilter());
});

// Industry filter
document.getElementById('industryFilter').addEventListener('change', function() {
    const searchTerm = document.getElementById('companySearch').value.toLowerCase();
    filterCompanies(searchTerm, this.value, getJobTypeFilter());
});

// Job type filter
document.getElementById('jobTypeFilter').addEventListener('change', function() {
    const searchTerm = document.getElementById('companySearch').value.toLowerCase();
    filterCompanies(searchTerm, getIndustryFilter(), this.value);
});

// Get current industry filter value
function getIndustryFilter() {
    return document.getElementById('industryFilter').value;
}

// Get current job type filter value
function getJobTypeFilter() {
    return document.getElementById('jobTypeFilter').value;
}

// Filter companies based on search term and filters
function filterCompanies(searchTerm, industryFilter, jobTypeFilter) {
    const companyCards = document.querySelectorAll('.company-card');
    
    companyCards.forEach(card => {
        const companyName = card.querySelector('.company-name').textContent.toLowerCase();
        const industry = card.getAttribute('data-industry').toLowerCase();
        
        // Check if company matches all filters
        const matchesSearch = companyName.includes(searchTerm);
        const matchesIndustry = !industryFilter || industry === industryFilter.toLowerCase();
        
        // For job type filter, we would need to add job type data to the cards
        // This is a placeholder for now
        const matchesJobType = !jobTypeFilter || true;
        
        if (matchesSearch && matchesIndustry && matchesJobType) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Near Me functionality
document.getElementById('nearMeBtn').addEventListener('click', function() {
    const statusElement = document.getElementById('nearMeStatus');
    const distanceFilter = document.getElementById('distanceFilter');
    
    statusElement.style.display = 'inline-block';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                
                // Center map on user location
                map.setView([userLat, userLng], 13);
                
                // Add user marker
                const userMarker = L.marker([userLat, userLng], {
                    icon: L.divIcon({
                        className: 'user-location-marker',
                        html: '<i class="fas fa-user-circle"></i>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    })
                }).addTo(map);
                userMarker.bindPopup("Your Location").openPopup();
                
                // Show distance filter
                distanceFilter.style.display = 'inline-block';
                
                // Filter companies by distance
                filterCompaniesByDistance(userLat, userLng, parseFloat(distanceFilter.value));
                
                // Update status
                statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Location found!';
                setTimeout(() => {
                    statusElement.style.display = 'none';
                }, 3000);
            },
            function(error) {
                console.error("Error getting location:", error);
                statusElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Could not get your location';
                setTimeout(() => {
                    statusElement.style.display = 'none';
                }, 3000);
            }
        );
    } else {
        statusElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Geolocation not supported by your browser';
        setTimeout(() => {
            statusElement.style.display = 'none';
        }, 3000);
    }
});

// Distance filter change event
document.getElementById('distanceFilter').addEventListener('change', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                filterCompaniesByDistance(
                    position.coords.latitude,
                    position.coords.longitude,
                    parseFloat(document.getElementById('distanceFilter').value)
                );
            }
        );
    }
});

// Filter companies by distance
function filterCompaniesByDistance(userLat, userLng, maxDistance) {
    const companyCards = document.querySelectorAll('.company-card');
    
    companyCards.forEach(card => {
        const companyLat = parseFloat(card.getAttribute('data-lat'));
        const companyLng = parseFloat(card.getAttribute('data-lng'));
        
        // Calculate distance using Haversine formula
        const distance = calculateDistance(userLat, userLng, companyLat, companyLng);
        
        if (distance <= maxDistance) {
            card.style.display = 'block';
            // Add distance info to the card
            let distanceInfo = card.querySelector('.distance-info');
            if (!distanceInfo) {
                distanceInfo = document.createElement('div');
                distanceInfo.className = 'distance-info';
                card.appendChild(distanceInfo);
            }
            distanceInfo.innerHTML = `<i class="fas fa-route"></i> ${distance.toFixed(1)} km from you`;
        } else {
            card.style.display = 'none';
        }
    });
}

// Calculate distance between two points using Haversine formula
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

// Add geocoder control to search for locations
L.Control.geocoder().addTo(map);