<?php
// Database connection
require_once '../includes/db.php';

session_start(); // Start the session to store login information

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_type = $_POST['login_type'];
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];
    $remember_username = isset($_POST['remember_username']) ? true : false;

    if ($login_type == 'graduate') {
        $sql = "SELECT * FROM users WHERE ic_number = ? AND user_type = 'graduate'";
    } else {
        $sql = "SELECT * FROM users WHERE email = ? AND user_type = 'company'";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type'];

            // Remember the username if the checkbox is checked
            if ($remember_username) {
                // Set cookie to remember the username for 30 days based on login type
                setcookie("remember_username_$login_type", $identifier, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // If not checked, remove the cookie
                setcookie("remember_username_$login_type", '', time() - 3600, "/");
            }

            if ($login_type == 'graduate') {
                header("Location: /Website/main/graduate_dashboard.php");
            } else {
                header("Location: /Website/company_profile/company_dashboard.php");
            }
            exit();
        } else {
            // Store error message in session instead of echoing it
            $_SESSION['login_error'] = "Invalid password.";
            $_SESSION['login_type'] = $login_type; // Remember which tab was active
            header("Location: /Website/index.php");
            exit();
        }
    } else {
        // Store error message in session instead of echoing it
        if ($login_type == 'graduate') {
            $_SESSION['login_error'] = "No graduate found with that IC number.";
        } else {
            $_SESSION['login_error'] = "No company found with that email.";
        }
        $_SESSION['login_type'] = $login_type; // Remember which tab was active
        header("Location: /Website/index.php");
        exit();
    }
}
?>

