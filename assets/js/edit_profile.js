// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set up tab navigation
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding content
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Save All functionality - Updated to collect all form values
    document.getElementById('saveAllForm').addEventListener('submit', function(event) {
        // Prevent default form submission
        event.preventDefault();
        
        // Create a FormData object
        const formData = new FormData(this);
        
        // Collect values from all visible form fields
        formData.set('companyName', document.querySelector('input[name="companyName"]')?.value || '');
        formData.set('tagline', document.querySelector('input[name="tagline"]')?.value || '');
        formData.set('location', document.querySelector('input[name="location"]')?.value || '');
        formData.set('contactInfo', document.querySelector('input[name="contactInfo"]')?.value || '');
        formData.set('foundingDate', document.querySelector('input[name="foundingDate"]')?.value || '');
        formData.set('founders', document.querySelector('input[name="founders"]')?.value || '');
        formData.set('milestones', document.querySelector('textarea[name="milestones"]')?.value || '');
        formData.set('mission', document.querySelector('textarea[name="mission"]')?.value || '');
        formData.set('vision', document.querySelector('textarea[name="vision"]')?.value || '');
        formData.set('products', document.querySelector('textarea[name="products"]')?.value || '');
        formData.set('usp', document.querySelector('input[name="usp"]')?.value || '');
        formData.set('awards', document.querySelector('textarea[name="awards"]')?.value || '');
        formData.set('testimonials', document.querySelector('textarea[name="testimonials"]')?.value || '');
        formData.set('about_us', document.querySelector('textarea[name="about_us"]')?.value || '');
        
        // Handle file uploads
        const logoInput = document.querySelector('#logoUpload');
        if (logoInput && logoInput.files.length > 0) {
            formData.set('logoUpload', logoInput.files[0]);
        }
        
        const officePhotoInput = document.querySelector('#officePhotoUpload');
        if (officePhotoInput && officePhotoInput.files.length > 0) {
            formData.set('officePhotoUpload', officePhotoInput.files[0]);
        }
        
        const infographicInput = document.querySelector('#infographicUpload');
        if (infographicInput && infographicInput.files.length > 0) {
            formData.set('infographicUpload', infographicInput.files[0]);
        }
        
        // Submit the form using fetch API
        fetch('edit_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                alert('All changes saved successfully!');
                window.location.reload();
            } else {
                alert('Error saving changes. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving changes.');
        });
    });
});