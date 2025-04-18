<?php
include '../includes/db_connection.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Get parameters
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Validate parameters
if ($job_id <= 0 || $user_id <= 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

// Check if the requesting user is the same as the application user
if ($_SESSION['user_id'] != $user_id) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if email_sent column exists, add if not
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'email_sent'");
if (mysqli_num_rows($column_check) == 0) {
    // Add the column if it doesn't exist
    mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN email_sent TINYINT(1) DEFAULT 0");
}

// Get application status
$status_sql = "SELECT status, queue_position FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
$status_result = mysqli_query($conn, $status_sql);

if ($status_result && mysqli_num_rows($status_result) > 0) {
    $application = mysqli_fetch_assoc($status_result);
    echo json_encode([
        'status' => $application['status'],
        'queue_position' => $application['queue_position']
    ]);
} else {
    echo json_encode(['error' => 'Application not found']);
}
?>