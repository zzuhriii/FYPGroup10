let map;
let marker;

// Initialize the map when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Get default coordinates from the form fields
    const defaultLat = parseFloat(document.getElementById('latitude').value);
    const defaultLng = parseFloat(document.getElementById('longitude').value);
    
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
    
    // Toggle instructions visibility
    document.getElementById('toggleInstructions').addEventListener('click', function() {
        const instructions = document.getElementById('mapInstructions');
        const button = document.getElementById('toggleInstructions');
        
        if (instructions.style.display === 'none') {
            instructions.style.display = 'block';
            button.innerHTML = '<i class="fas fa-info-circle"></i> Hide Map Instructions <i class="fas fa-chevron-up"></i>';
        } else {
            instructions.style.display = 'none';
            button.innerHTML = '<i class="fas fa-info-circle"></i> Show Map Instructions <i class="fas fa-chevron-down"></i>';
        }
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