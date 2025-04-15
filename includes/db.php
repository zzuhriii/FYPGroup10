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
    reset_token VARCHAR(255) NULL,
    reset_token_expiry DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Users table update - first add the column if it doesn't exist, then modify it
$sql = "ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS user_type enum('graduate','company','admin') DEFAULT 'graduate',
    ADD COLUMN IF NOT EXISTS phone varchar(15) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS profile_pic varchar(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS cv varchar(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS ic_number varchar(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS programme varchar(50) DEFAULT NULL";
if ($conn->query($sql) === FALSE) {
    die("Error updating users table: " . $conn->error);
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
    job_ID int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    job_UserID int(11) NOT NULL,
    job_Title varchar(256) NOT NULL,
    job_Description text NOT NULL,
    job_Created timestamp NOT NULL DEFAULT current_timestamp(),
    job_Category varchar(256) NOT NULL,
    job_Vacancy int(50) NOT NULL,
    job_Offered datetime NOT NULL,
    job_location varchar(255) DEFAULT NULL,
    application_deadline date DEFAULT NULL,
    programme varchar(10) DEFAULT NULL,
    company_id int(11) DEFAULT NULL,
    salary_estimation VARCHAR(100) DEFAULT NULL
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
    FOREIGN KEY (job_id) REFERENCES jobs(job_ID)
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating applications table: " . $conn->error);
}

// Education table
$sql = "CREATE TABLE IF NOT EXISTS education (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    education_level VARCHAR(50) NOT NULL,
    institution VARCHAR(255) NOT NULL,
    field_of_study VARCHAR(255) NOT NULL,
    graduation_year INT(11) NOT NULL,
    certificate VARCHAR(255) DEFAULT NULL,
    KEY user_id (user_id),
    CONSTRAINT education_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating education table: " . $conn->error);
}

// Work Experience table
$sql = "CREATE TABLE IF NOT EXISTS work_experience (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    company VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    description TEXT NOT NULL,
    KEY user_id (user_id),
    CONSTRAINT work_experience_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating work_experience table: " . $conn->error);
}

// Achievements table
$sql = "CREATE TABLE IF NOT EXISTS achievements (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    year INT(11) NOT NULL,
    certificate VARCHAR(255) DEFAULT NULL,
    KEY user_id (user_id),
    CONSTRAINT achievements_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating achievements table: " . $conn->error);
}

// Students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(50) NOT NULL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT(11) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    cgpa DECIMAL(3,2) NOT NULL,
    school VARCHAR(255) NOT NULL
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating students table: " . $conn->error);
}

//
$sql = "CREATE TABLE IF NOT EXISTS company_profile (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    company_name VARCHAR(255) DEFAULT NULL,
    tagline VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    contact_info TEXT DEFAULT NULL,
    founding_date VARCHAR(100) DEFAULT NULL,
    founders TEXT DEFAULT NULL,
    milestones TEXT DEFAULT NULL,
    mission TEXT DEFAULT NULL,
    vision TEXT DEFAULT NULL,
    products TEXT DEFAULT NULL,
    usp TEXT DEFAULT NULL,
    awards TEXT DEFAULT NULL,
    testimonials TEXT DEFAULT NULL,
    about_us TEXT DEFAULT NULL,
    logo MEDIUMBLOB DEFAULT NULL,
    office_photo MEDIUMBLOB DEFAULT NULL,
    infographic MEDIUMBLOB DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating company_profile table: " . $conn->error);
}

// Job applications table
$sql = "CREATE TABLE IF NOT EXISTS job_applications (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    job_id int(11) NOT NULL,
    user_id int(11) NOT NULL,
    cover_letter text DEFAULT NULL,
    application_date timestamp NOT NULL DEFAULT current_timestamp(),
    status enum('pending','accepted','declined') DEFAULT 'pending',
    feedback text DEFAULT NULL,
    decline_reason text DEFAULT NULL,
    FOREIGN KEY (job_id) REFERENCES jobs(job_ID) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === FALSE) {
    die("Error creating job_applications table: " . $conn->error);
}

// Add salary_estimation column to jobs table if it doesn't exist
$sql = "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS salary_estimation VARCHAR(100) DEFAULT NULL";
if ($conn->query($sql) === FALSE) {
    // Don't die on error, just log it
    error_log("Error adding salary_estimation column: " . $conn->error);
}




?>



