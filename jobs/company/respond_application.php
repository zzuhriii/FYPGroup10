<?php
    // Include the header file which already has the database connection
    include '../header.php';
    
    // Check if user is logged in as a company
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
        header("Location: /Website/index.php");
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Check if application ID and action are provided
    if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['action'])) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $application_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = mysqli_real_escape_string($conn, $_GET['action']);
    
    // Validate action
    if ($action != 'accept' && $action != 'decline') {
        header("Location: manage_jobs.php");
        exit();
    }
    
    // Get application details to find the job_id
    $app_sql = "SELECT job_id, user_id FROM job_applications WHERE id = '$application_id'";
    $app_result = mysqli_query($conn, $app_sql);
    
    if (mysqli_num_rows($app_result) == 0) {
        header("Location: manage_jobs.php");
        exit();
    }
    
    $application = mysqli_fetch_assoc($app_result);
    $job_id = $application['job_id'];
    $applicant_id = $application['user_id'];
    
    // If action is decline and no POST data yet, show the form to enter decline reason
    if ($action == 'decline' && !isset($_POST['submit'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Decline Application</title>
            <link rel="stylesheet" href="/Website/assets/css/styles.css">
            <style>
                .decline-form {
                    max-width: 600px;
                    margin: 2rem auto;
                    padding: 2rem;
                    background-color: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .form-group {
                    margin-bottom: 1.5rem;
                }
                label {
                    display: block;
                    margin-bottom: 0.5rem;
                    font-weight: bold;
                }
                textarea {
                    width: 100%;
                    padding: 0.75rem;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    min-height: 150px;
                }
                .btn-group {
                    display: flex;
                    justify-content: space-between;
                    margin-top: 1.5rem;
                }
                .btn {
                    padding: 0.75rem 1.5rem;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .btn-primary {
                    background-color: #092D74;
                    color: white;
                }
                .btn-secondary {
                    background-color: #6c757d;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="decline-form">
                <h2>Decline Application</h2>
                <p>Please provide a reason for declining this application:</p>
                
                <form method="POST" action="respond_application.php?id=<?php echo $application_id; ?>&action=decline">
                    <div class="form-group">
                        <label for="decline_reason">Reason for declining:</label>
                        <textarea id="decline_reason" name="decline_reason" required></textarea>
                    </div>
                    <div class="btn-group">
                        <a href="view_applications.php?job_id=<?php echo $job_id; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
    
    // Update application status
    $status = ($action == 'accept') ? 'accepted' : 'declined';
    
    if ($action == 'decline' && isset($_POST['submit'])) {
        // Get and sanitize the decline reason
        $decline_reason = mysqli_real_escape_string($conn, $_POST['decline_reason']);
        
        // Update with decline reason
        $update_sql = "UPDATE job_applications SET status = '$status', decline_reason = '$decline_reason' WHERE id = '$application_id'";
    } else {
        // Regular update for acceptance
        $update_sql = "UPDATE job_applications SET status = '$status' WHERE id = '$application_id'";
    }
    
    // Debug: Print the SQL query
    // echo "<!-- Debug SQL: $update_sql -->";
    
    if (mysqli_query($conn, $update_sql)) {
        $message = "Application has been " . $status . " successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating application: " . mysqli_error($conn);
        $messageType = "error";
    }
    
    // Redirect back to the view applications page
    header("Location: view_applications.php?job_id=" . $job_id . "&message=" . urlencode($message) . "&messageType=" . $messageType);
    exit();
?>