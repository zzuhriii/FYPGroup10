<?php

// reference https://www.youtube.com/watch?v=m52ljs78S24&list=PL0eyrZgxdwhwwQQZA79OzYwl5ewA7HQih 

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