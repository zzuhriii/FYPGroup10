<?php
include '../db_connect.php';

// Check if admin_users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admin_users'");
if (mysqli_num_rows($table_check) == 0) {
    // Create table
    $create_table = "CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table) === TRUE) {
        echo "Admin users table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
        exit();
    }
}

// Create default admin user
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Change this password!
$email = "admin@example.com";

$check_sql = "SELECT * FROM admin_users WHERE username = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows == 0) {
    $insert_sql = "INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $username, $password, $email);
    
    if ($insert_stmt->execute()) {
        echo "Default admin user created successfully<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "<strong>Please change this password immediately after logging in!</strong>";
    } else {
        echo "Error creating admin user: " . $insert_stmt->error;
    }
} else {
    echo "Admin user already exists";
}
?>