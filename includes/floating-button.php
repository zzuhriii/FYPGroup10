<?php
// Check if session is started, if not start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug flag - set to true to see debugging information
$debug = false;

// Add debugging information if enabled
if ($debug) {
    echo "<!-- Floating button debug info: ";
    echo "Session exists: " . (isset($_SESSION) ? "Yes" : "No") . ", ";
    echo "User type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : "Not set");
    echo " -->";
}

// Check user type and set appropriate dashboard URL
if (isset($_SESSION['user_type'])) {
    $dashboard_url = '';
    $dashboard_text = 'Dashboard';
    
    // Set dashboard URL based on user type
    if ($_SESSION['user_type'] === 'student') {
        $dashboard_url = '/Website/student_profile/student_dashboard.php';
        $dashboard_text = 'Student Dashboard';
    } elseif ($_SESSION['user_type'] === 'graduate') {
        $dashboard_url = '/Website/main/graduate_dashboard.php';
        $dashboard_text = 'Graduate Dashboard';
    } elseif ($_SESSION['user_type'] === 'company') {
        $dashboard_url = '/Website/company_profile/company_dashboard.php';
        $dashboard_text = 'Company Dashboard';
    } elseif ($_SESSION['user_type'] === 'admin') {
        $dashboard_url = '/Website/admin/admin_dashboard.php';
        $dashboard_text = 'Admin Dashboard';
    }
    
    // Only display the button if the user is logged in and has a valid dashboard
    if (!empty($dashboard_url)) {
        echo '<style>
        .floating-back-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #0056b3;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-decoration: none;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .floating-back-btn i {
            font-size: 24px;
        }
        
        .floating-back-btn:hover {
            background-color: #004494;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }
        
        .floating-back-btn::after {
            content: "' . $dashboard_text . '";
            position: absolute;
            top: -35px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            white-space: nowrap;
        }
        
        .floating-back-btn:hover::after {
            opacity: 1;
        }
        </style>';
        
        echo '<a href="' . $dashboard_url . '" class="floating-back-btn" title="' . $dashboard_text . '">';
        echo '<i class="fas fa-home"></i>';
        echo '</a>';
    } else if ($debug) {
        echo "<!-- No valid dashboard URL found -->";
    }
} else if ($debug) {
    echo "<!-- User type not set in session -->";
}
?>