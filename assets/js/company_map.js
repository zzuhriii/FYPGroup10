document.addEventListener('DOMContentLoaded', function() {
    // Check if map element exists
    const mapElement = document.getElementById('map');
    if (!mapElement) return;
    
    // Get latitude and longitude from data attributes
    const latitude = mapElement.getAttribute('data-lat');
    const longitude = mapElement.getAttribute('data-lng');
    const companyName = mapElement.getAttribute('data-company');
    const address = mapElement.getAttribute('data-address');
    
    if (!latitude || !longitude) return;
    
    // Initialize the map
    const map = L.map('map').setView([latitude, longitude], 15);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Add marker for company location
    const marker = L.marker([latitude, longitude], {
        title: companyName
    }).addTo(map);
    
    // Create popup content
    const popupContent = `
        <div style="max-width: 200px; padding: 10px;">
            <h3 style="margin-top: 0; color: #4285f4;">${companyName}</h3>
            <p style="margin-bottom: 5px;">${address}</p>
            <a href="https://www.openstreetmap.org/directions?from=&to=${latitude}%2C${longitude}" 
               target="_blank" style="color: #4285f4; text-decoration: none;">
               <i class="fas fa-directions"></i> Get Directions
            </a>
        </div>
    `;
    
    // Add popup to marker
    marker.bindPopup(popupContent);
});