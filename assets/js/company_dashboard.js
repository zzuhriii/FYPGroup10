// Function to toggle dropdown menu
function toggleDropdown(event) {
    event.preventDefault();
    var dropdown = document.getElementById("dashboardDropdown");
    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
    } else {
        dropdown.style.display = "block";
    }
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
    if (!event.target.matches('.dashboard-link') && !event.target.matches('.fa-caret-down')) {
        var dropdown = document.getElementById("dashboardDropdown");
        if (dropdown.style.display === "block") {
            dropdown.style.display = "none";
        }
    }
}

// Check if welcome message was previously dismissed
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('welcomeDismissed') === 'true') {
        document.getElementById('welcomeMessage').style.display = 'none';
    }
});

// Function to dismiss welcome message
function dismissWelcome() {
    document.getElementById('welcomeMessage').style.display = 'none';
    localStorage.setItem('welcomeDismissed', 'true');
}