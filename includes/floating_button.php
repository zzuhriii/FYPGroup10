<?php
/**
 * Floating Button Component
 * 
 * This file provides a reusable floating button component that can be included across multiple pages.
 * Usage: include_once '../includes/floating_button.php'; // Adjust the path as needed
 * Then call: displayFloatingButton('Back to Dashboard', '/Website/company_profile/company_dashboard.php', 'fa-arrow-left');
 */

/**
 * Display a floating button with customizable text, link, and icon
 * 
 * @param string $text The button text
 * @param string $link The URL the button links to
 * @param string $iconClass The Font Awesome icon class (without the 'fas' prefix)
 * @param string $position The position of the button ('bottom-right', 'bottom-left', 'top-right', 'top-left')
 */
function displayFloatingButton($text, $link, $iconClass = 'fa-arrow-left', $position = 'bottom-right') {
    // Determine CSS classes based on position
    $positionClass = '';
    
    switch($position) {
        case 'bottom-left':
            $positionClass = 'bottom: 30px; left: 30px;';
            break;
        case 'top-right':
            $positionClass = 'top: 30px; right: 30px;';
            break;
        case 'top-left':
            $positionClass = 'top: 30px; left: 30px;';
            break;
        case 'bottom-right':
        default:
            $positionClass = 'bottom: 30px; right: 30px;';
            break;
    }
    
    // Output the button HTML and CSS
    echo <<<HTML
    <style>
        .floating-btn {
            position: fixed;
            {$positionClass}
            background-color: #4361ee;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 1rem;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.4);
            cursor: pointer;
            z-index: 100;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .floating-btn:hover {
            background-color: #3a56d4;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.5);
        }
        
        .floating-btn i {
            margin-right: 10px;
        }
    </style>
    
    <a href="{$link}" class="floating-btn">
        <i class="fas {$iconClass}"></i> {$text}
    </a>
HTML;
}
?>