<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketing_day";

// Connect to MySQL server without specifying the database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it does not exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}

// Use the database
$conn->select_db($dbname);

// Create tables if they don't exist
// Users table for graduates
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// CV table for storing graduate CVs
$sql = "CREATE TABLE IF NOT EXISTS cvs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cv_file VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating cvs table: " . $conn->error);
}

// Jobs table for company job postings
$sql = "CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating jobs table: " . $conn->error);
}

// Applications table for job applications
$sql = "CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (job_id) REFERENCES jobs(id)
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating applications table: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Marketing Day</title>
    <link rel="stylesheet" href="css/index.css">
    <img src="pblogo.png" alt="Your Image" class="top-left-image">
    
</head>
<body>

<header>
    <h1>Welcome to Politeknik Marketing Day</h1>
</header>

<main>
    <section class="intro">
        <h2>Welcome! Are you a graduate looking for a job or a company looking to post jobs?</h2>
        <p>We provide a platform for graduates to upload their CVs and apply for job openings, as well as for companies to post available job positions.</p>
        
        <button class="register-btn" onclick="window.location.href='register_graduate.php'">Register as Graduate</button>
        <button class="company-btn" onclick="window.location.href='register_company.php'">Register as Company</button>
    </section>
</main>

</body>
</html>
