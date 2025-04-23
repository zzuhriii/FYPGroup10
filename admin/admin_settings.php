<?php
session_start();
include '../includes/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Check if admin table exists, if not create it
$check_admin_table_sql = "SHOW TABLES LIKE 'admin'";
$check_admin_table_result = $conn->query($check_admin_table_sql);

if ($check_admin_table_result->num_rows == 0) {
    // Create admin table
    $create_admin_table_sql = "CREATE TABLE admin (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_admin_table_sql);
    
    // Insert default admin user if table was just created
    $default_username = 'admin';
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $default_email = 'admin@pbjobportal.com';
    $default_name = 'Administrator';
    
    $insert_admin_sql = "INSERT INTO admin (username, password, email, name) VALUES (?, ?, ?, ?)";
    $insert_admin_stmt = $conn->prepare($insert_admin_sql);
    $insert_admin_stmt->bind_param("ssss", $default_username, $default_password, $default_email, $default_name);
    $insert_admin_stmt->execute();
    
    // Update session with the new admin ID
    $_SESSION['admin_id'] = $conn->insert_id;
    $_SESSION['admin_username'] = $default_username;
}

// Get admin information
$admin_query = "SELECT * FROM admin WHERE id = ?";
$admin_stmt = $conn->prepare($admin_query);
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();
$admin_data = $admin_result->fetch_assoc();

// Handle profile update
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    
    $update_sql = "UPDATE admin SET username = ?, email = ?, name = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $username, $email, $name, $admin_id);
    
    if ($update_stmt->execute()) {
        $message = "Profile updated successfully";
        $messageType = "success";
        
        // Update session data
        $_SESSION['admin_username'] = $username;
        
        // Refresh admin data
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin_data = $admin_result->fetch_assoc();
    } else {
        $message = "Error updating profile: " . $conn->error;
        $messageType = "error";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $password_query = "SELECT password FROM admin WHERE id = ?";
    $password_stmt = $conn->prepare($password_query);
    $password_stmt->bind_param("i", $admin_id);
    $password_stmt->execute();
    $password_result = $password_stmt->get_result();
    $password_data = $password_result->fetch_assoc();
    
    if (password_verify($current_password, $password_data['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_password_sql = "UPDATE admin SET password = ? WHERE id = ?";
            $update_password_stmt = $conn->prepare($update_password_sql);
            $update_password_stmt->bind_param("si", $hashed_password, $admin_id);
            
            if ($update_password_stmt->execute()) {
                $password_message = "Password changed successfully";
                $passwordMessageType = "success";
            } else {
                $password_message = "Error changing password: " . $conn->error;
                $passwordMessageType = "error";
            }
        } else {
            $password_message = "New passwords do not match";
            $passwordMessageType = "error";
        }
    } else {
        $password_message = "Current password is incorrect";
        $passwordMessageType = "error";
    }
}

// Handle site settings update
if (isset($_POST['update_settings'])) {
    $site_name = $_POST['site_name'];
    $site_email = $_POST['site_email'];
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
    
    // Check if settings table exists, if not create it
    $check_table_sql = "SHOW TABLES LIKE 'site_settings'";
    $check_table_result = $conn->query($check_table_sql);
    
    if ($check_table_result->num_rows == 0) {
        // Create settings table
        $create_table_sql = "CREATE TABLE site_settings (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            setting_name VARCHAR(255) NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->query($create_table_sql);
    }
    
    // Update or insert site name
    $update_site_name_sql = "INSERT INTO site_settings (setting_name, setting_value) 
                            VALUES ('site_name', ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?";
    $update_site_name_stmt = $conn->prepare($update_site_name_sql);
    $update_site_name_stmt->bind_param("ss", $site_name, $site_name);
    $update_site_name_stmt->execute();
    
    // Update or insert site email
    $update_site_email_sql = "INSERT INTO site_settings (setting_name, setting_value) 
                             VALUES ('site_email', ?) 
                             ON DUPLICATE KEY UPDATE setting_value = ?";
    $update_site_email_stmt = $conn->prepare($update_site_email_sql);
    $update_site_email_stmt->bind_param("ss", $site_email, $site_email);
    $update_site_email_stmt->execute();
    
    // Update or insert maintenance mode
    $update_maintenance_sql = "INSERT INTO site_settings (setting_name, setting_value) 
                              VALUES ('maintenance_mode', ?) 
                              ON DUPLICATE KEY UPDATE setting_value = ?";
    $update_maintenance_stmt = $conn->prepare($update_maintenance_sql);
    $update_maintenance_stmt->bind_param("ss", $maintenance_mode, $maintenance_mode);
    $update_maintenance_stmt->execute();
    
    $settings_message = "Site settings updated successfully";
    $settingsMessageType = "success";
}

// Get current site settings
$site_settings = [];

// Check if site_settings table exists before querying it
$check_settings_table_sql = "SHOW TABLES LIKE 'site_settings'";
$check_settings_table_result = $conn->query($check_settings_table_sql);

if ($check_settings_table_result->num_rows > 0) {
    // Table exists, proceed with query
    $settings_query = "SELECT * FROM site_settings";
    $settings_result = $conn->query($settings_query);
    
    if ($settings_result && $settings_result->num_rows > 0) {
        while ($row = $settings_result->fetch_assoc()) {
            $site_settings[$row['setting_name']] = $row['setting_value'];
        }
    }
} else {
    // Create settings table
    $create_table_sql = "CREATE TABLE site_settings (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        setting_name VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->query($create_table_sql);
    
    // Initialize with default values
    $default_settings = [
        ['site_name', 'PB Job Portal'],
        ['site_email', 'contact@pbjobportal.com'],
        ['maintenance_mode', '0']
    ];
    
    $insert_setting_sql = "INSERT INTO site_settings (setting_name, setting_value) VALUES (?, ?)";
    $insert_setting_stmt = $conn->prepare($insert_setting_sql);
    
    foreach ($default_settings as $setting) {
        $insert_setting_stmt->bind_param("ss", $setting[0], $setting[1]);
        $insert_setting_stmt->execute();
        $site_settings[$setting[0]] = $setting[1];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid #3d5166;
        }
        
        .sidebar-header img {
            max-width: 120px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: #34495e;
        }
        
        .menu-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 15px;
            color: #6c757d;
        }
        
        .logout-btn {
            background-color: #f8f9fa;
            color: #6c757d;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background-color: #e9ecef;
        }
        
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .settings-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .settings-card h2 {
            color: #2c3e50;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .form-check input {
            margin-right: 10px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="/Website/assets/images/pblogo.png" alt="Politeknik Brunei Logo">
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="admin_graduates.php" class="menu-item">
                    <i class="fas fa-user-graduate"></i> <span>Manage Graduates</span>
                </a>
                <a href="admin_companies.php" class="menu-item">
                    <i class="fas fa-building"></i> <span>Manage Companies</span>
                </a>
                <a href="admin_jobs.php" class="menu-item">
                    <i class="fas fa-briefcase"></i> <span>Manage Jobs</span>
                </a>
                <a href="admin_applications.php" class="menu-item">
                    <i class="fas fa-file-alt"></i> <span>Applications</span>
                </a>
                <a href="admin_settings.php" class="menu-item active">
                    <i class="fas fa-cog"></i> <span>Settings</span>
                </a>
            </div>
        </div>
        
        <div class="content">
            <div class="header">
                <h1>Admin Settings</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="settings-container">
                <div class="settings-card">
                    <h2>Profile Settings</h2>
                    
                    <?php if (isset($message)): ?>
                        <div class="message <?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($admin_data['name']); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h2>Change Password</h2>
                    
                    <?php if (isset($password_message)): ?>
                        <div class="message <?php echo $passwordMessageType; ?>">
                            <?php echo $password_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h2>Site Settings</h2>
                    
                    <?php if (isset($settings_message)): ?>
                        <div class="message <?php echo $settingsMessageType; ?>">
                            <?php echo $settings_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" class="form-control" 
                                   value="<?php echo isset($site_settings['site_name']) ? htmlspecialchars($site_settings['site_name']) : 'PB Job Portal'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="site_email">Contact Email</label>
                            <input type="email" id="site_email" name="site_email" class="form-control" 
                                   value="<?php echo isset($site_settings['site_email']) ? htmlspecialchars($site_settings['site_email']) : 'contact@pbjobportal.com'; ?>">
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   <?php echo (isset($site_settings['maintenance_mode']) && $site_settings['maintenance_mode'] == 1) ? 'checked' : ''; ?>>
                            <label for="maintenance_mode">Maintenance Mode</label>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h2>System Information</h2>
                    
                    <div class="form-group">
                        <label>PHP Version</label>
                        <input type="text" class="form-control" value="<?php echo phpversion(); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>MySQL Version</label>
                        <input type="text" class="form-control" value="<?php echo $conn->server_info; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Server Software</label>
                        <input type="text" class="form-control" value="<?php echo $_SERVER['SERVER_SOFTWARE']; ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Login</label>
                        <input type="text" class="form-control" value="<?php echo isset($admin_data['last_login']) ? date('M d, Y H:i:s', strtotime($admin_data['last_login'])) : 'Not available'; ?>" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>