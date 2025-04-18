<?php
// Skip if not logged in as graduate
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'graduate') {
    return;
}

$user_id = $_SESSION['user_id'];

// Check if job_applications table exists
$has_applications = false;
$applications = [];

$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'job_applications'");
if (mysqli_num_rows($table_check) > 0) {
    // Check if queue_position column exists, add if not
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'queue_position'");
    if (mysqli_num_rows($column_check) == 0) {
        // Add the column if it doesn't exist
        mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN queue_position INT");
        
        // Initialize queue positions for existing applications
        $update_queue = "SET @counter = 0; 
                        UPDATE job_applications 
                        SET queue_position = (@counter:=@counter+1) 
                        WHERE 1 
                        ORDER BY application_date ASC";
        mysqli_multi_query($conn, $update_queue);
        
        // Clear results to allow next query
        while (mysqli_next_result($conn)) {
            if ($result = mysqli_store_result($conn)) {
                mysqli_free_result($result);
            }
        }
    }
    
    // Check if email_sent column exists, add if not
    $email_column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'email_sent'");
    if (mysqli_num_rows($email_column_check) == 0) {
        // Add the column if it doesn't exist
        mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN email_sent TINYINT(1) DEFAULT 0");
    }
    
    // Check if decline_reason column exists, add if not
    $decline_column_check = mysqli_query($conn, "SHOW COLUMNS FROM job_applications LIKE 'decline_reason'");
    if (mysqli_num_rows($decline_column_check) == 0) {
        // Add the column if it doesn't exist
        mysqli_query($conn, "ALTER TABLE job_applications ADD COLUMN decline_reason TEXT");
    }
    
    // Get all active applications for this user
    $apps_sql = "SELECT ja.job_id, ja.queue_position, ja.status, j.job_Title 
                FROM job_applications ja 
                JOIN jobs j ON ja.job_id = j.job_ID 
                WHERE ja.user_id = ? 
                AND ja.status NOT IN ('declined', 'rejected')
                ORDER BY ja.application_date DESC";
    
    $stmt = $conn->prepare($apps_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $apps_result = $stmt->get_result();
    
    if ($apps_result && mysqli_num_rows($apps_result) > 0) {
        $has_applications = true;
        while ($app = mysqli_fetch_assoc($apps_result)) {
            $applications[] = $app;
        }
    }
}

// Only proceed if user has applications
if ($has_applications):
?>

<!-- Include the CSS file -->
<link rel="stylesheet" href="/Website/assets/css/queue_tracker.css">

<!-- Queue Tracker Tab -->
<div class="global-queue-tab" id="globalQueueTab">
    <div class="notification-dot" id="queueNotification"></div>
    <div class="global-queue-tab-label">My Queue</div>
</div>

<!-- Queue Tracker Popup -->
<div class="global-queue-popup" id="globalQueuePopup">
    <div class="global-queue-popup-header">
        <span class="global-queue-popup-title">Your Application Queues</span>
        <button class="global-queue-popup-close" id="globalQueueClose">&times;</button>
    </div>
    
    <div class="global-queue-list">
        <?php foreach ($applications as $app): ?>
        <div class="global-queue-item">
            <div class="global-queue-item-title"><?php echo htmlspecialchars($app['job_Title']); ?></div>
            <div class="global-queue-item-number">Queue #<?php echo $app['queue_position']; ?></div>
            <div class="global-queue-item-status status-<?php echo strtolower($app['status']); ?>">
                Status: <?php echo ucfirst($app['status']); ?>
            </div>
            <a href="/Website/jobs/view_job.php?id=<?php echo $app['job_id']; ?>" class="global-queue-item-link">View Details &rarr;</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Include the JavaScript file -->
<script src="/Website/assets/js/queue_tracker.js"></script>

<?php endif; ?>