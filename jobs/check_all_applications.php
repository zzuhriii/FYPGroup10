<?php
include '../includes/db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$hasChanges = false;

// Get current application statuses from session if available
$current_statuses = $_SESSION['application_statuses'] ?? [];

// Get latest application statuses
$status_sql = "SELECT job_id, status FROM job_applications WHERE user_id = '$user_id'";
$status_result = mysqli_query($conn, $status_sql);

if ($status_result && mysqli_num_rows($status_result) > 0) {
    $latest_statuses = [];
    
    while ($row = mysqli_fetch_assoc($status_result)) {
        $job_id = $row['job_id'];
        $status = $row['status'];
        $latest_statuses[$job_id] = $status;
        
        // Check if status has changed
        if (!isset($current_statuses[$job_id]) || $current_statuses[$job_id] !== $status) {
            $hasChanges = true;
        }
    }
    
    // Update session with latest statuses
    $_SESSION['application_statuses'] = $latest_statuses;
}

echo json_encode(['hasChanges' => $hasChanges]);
?>