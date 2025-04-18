document.addEventListener('DOMContentLoaded', function() {
    const globalTab = document.getElementById('globalQueueTab');
    const globalPopup = document.getElementById('globalQueuePopup');
    const globalClose = document.getElementById('globalQueueClose');
    const queueNotification = document.getElementById('queueNotification');
    
    // Check if popup should be open from localStorage
    if (localStorage.getItem('globalQueueOpen') === 'true') {
        globalPopup.classList.add('show');
        globalTab.style.display = 'none';
    }
    
    // Open popup when tab is clicked
    globalTab.addEventListener('click', function() {
        globalPopup.classList.add('show');
        globalTab.style.display = 'none';
        localStorage.setItem('globalQueueOpen', 'true');
        
        // Clear notification when opening popup
        queueNotification.classList.remove('show');
        localStorage.removeItem('queueNotification');
    });
    
    // Close popup
    globalClose.addEventListener('click', function() {
        globalPopup.classList.remove('show');
        globalTab.style.display = 'block';
        localStorage.removeItem('globalQueueOpen');
    });
    
    // Show notification if there are status changes
    if (localStorage.getItem('queueNotification') === 'true') {
        queueNotification.classList.add('show');
    }
    
    // Check for updates every minute
    setInterval(function() {
        fetch('/Website/jobs/check_all_applications.php')
            .then(response => response.json())
            .then(data => {
                if (data.hasChanges) {
                    // Show notification if popup is not open
                    if (!globalPopup.classList.contains('show')) {
                        queueNotification.classList.add('show');
                        localStorage.setItem('queueNotification', 'true');
                    }
                    
                    // Refresh the page if there are changes and popup is open
                    if (globalPopup.classList.contains('show')) {
                        window.location.reload();
                    }
                }
            })
            .catch(error => console.error('Error checking applications:', error));
    }, 60000);
});