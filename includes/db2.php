<?php

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "merged_database";

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

// Create tables structure
// Courses table
$sql = "CREATE TABLE IF NOT EXISTS courses (
    course_id int(11) NOT NULL AUTO_INCREMENT,
    course_name varchar(255) NOT NULL,
    PRIMARY KEY (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating courses table: " . $conn->error);
}

// Job categories table
$sql = "CREATE TABLE IF NOT EXISTS job_categories (
    category_id int(11) NOT NULL AUTO_INCREMENT,
    category_name varchar(255) NOT NULL,
    PRIMARY KEY (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating job_categories table: " . $conn->error);
}

// Users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id int(11) NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    user_type enum('graduate','company') NOT NULL,
    phone varchar(15) DEFAULT NULL,
    profile_pic varchar(255) DEFAULT NULL,
    cv varchar(255) DEFAULT NULL,
    ic_number varchar(20) NOT NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expiry DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Jobs table (from company_database)
$sql = "CREATE TABLE IF NOT EXISTS jobs (
    job_ID int(11) NOT NULL AUTO_INCREMENT,
    job_Title varchar(256) NOT NULL,
    job_Description text NOT NULL,
    job_Category_id int(11) NOT NULL,
    job_Vacancy int(50) NOT NULL,
    job_Created datetime NOT NULL DEFAULT current_timestamp(),
    job_Updated datetime NOT NULL DEFAULT current_timestamp(),
    application_deadline datetime DEFAULT NULL,
    job_location enum('Brunei Muara','Kuala Belait','Tutong','Temburong') NOT NULL,
    job_Requirements text NOT NULL,
    minimum_salary int(11) DEFAULT NULL,
    maximum_salary int(11) DEFAULT NULL,
    is_expired tinyint(1) DEFAULT 0,
    PRIMARY KEY (job_ID),
    KEY fk_job_category (job_Category_id),
    CONSTRAINT fk_job_category FOREIGN KEY (job_Category_id) REFERENCES job_categories (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating jobs table: " . $conn->error);
}

// Marketing jobs table (renamed from the second database)
$sql = "CREATE TABLE IF NOT EXISTS marketing_jobs (
    id int(11) NOT NULL AUTO_INCREMENT,
    title varchar(100) NOT NULL,
    description text NOT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating marketing_jobs table: " . $conn->error);
}

// Job category courses table
$sql = "CREATE TABLE IF NOT EXISTS job_category_courses (
    job_category_id int(11) NOT NULL,
    course_id int(11) NOT NULL,
    PRIMARY KEY (job_category_id,course_id),
    KEY course_id (course_id),
    CONSTRAINT job_category_courses_ibfk_1 FOREIGN KEY (job_category_id) REFERENCES job_categories (category_id) ON DELETE CASCADE,
    CONSTRAINT job_category_courses_ibfk_2 FOREIGN KEY (course_id) REFERENCES courses (course_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating job_category_courses table: " . $conn->error);
}

// Education table
$sql = "CREATE TABLE IF NOT EXISTS education (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    education_level varchar(50) NOT NULL,
    institution varchar(255) NOT NULL,
    field_of_study varchar(255) NOT NULL,
    graduation_year int(11) NOT NULL,
    certificate varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT education_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating education table: " . $conn->error);
}

// Work Experience table
$sql = "CREATE TABLE IF NOT EXISTS work_experience (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    company varchar(255) NOT NULL,
    position varchar(255) NOT NULL,
    start_date date NOT NULL,
    end_date date DEFAULT NULL,
    description text NOT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT work_experience_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating work_experience table: " . $conn->error);
}

// Achievements table
$sql = "CREATE TABLE IF NOT EXISTS achievements (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    title varchar(255) NOT NULL,
    description text NOT NULL,
    year int(11) NOT NULL,
    certificate varchar(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT achievements_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating achievements table: " . $conn->error);
}

// CV table for storing graduate CVs
$sql = "CREATE TABLE IF NOT EXISTS cvs (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    cv_file varchar(255) NOT NULL,
    uploaded_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    CONSTRAINT cvs_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating cvs table: " . $conn->error);
}

// Applications table
$sql = "CREATE TABLE IF NOT EXISTS applications (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    job_id int(11) NOT NULL,
    applied_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY user_id (user_id),
    KEY job_id (job_id),
    CONSTRAINT applications_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT applications_ibfk_2 FOREIGN KEY (job_id) REFERENCES marketing_jobs (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating applications table: " . $conn->error);
}

// Students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    student_id varchar(50) NOT NULL,
    name varchar(100) NOT NULL,
    age int(11) NOT NULL,
    gender enum('Male','Female','Other') NOT NULL,
    cgpa decimal(3,2) NOT NULL,
    school varchar(255) NOT NULL,
    PRIMARY KEY (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
if ($conn->query($sql) === FALSE) {
    die("Error creating students table: " . $conn->error);
}

// Create triggers for the jobs table
$sql = "
DROP TRIGGER IF EXISTS update_is_expired;
DELIMITER //
CREATE TRIGGER update_is_expired BEFORE INSERT ON jobs FOR EACH ROW 
BEGIN
    IF NEW.application_deadline < CURDATE() THEN
        SET NEW.is_expired = TRUE;
    ELSE
        SET NEW.is_expired = FALSE;
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS update_is_expired_on_update;
DELIMITER //
CREATE TRIGGER update_is_expired_on_update BEFORE UPDATE ON jobs FOR EACH ROW 
BEGIN
    IF NEW.application_deadline < CURDATE() THEN
        SET NEW.is_expired = TRUE;
    ELSE
        SET NEW.is_expired = FALSE;
    END IF;
END//
DELIMITER ;
";

// Due to DELIMITER issues in PHP, let's execute the trigger creation separately
$conn->multi_query($sql);
while ($conn->more_results() && $conn->next_result()) {
    // flush multi_query results
}
if ($conn->error) {
    die("Error creating triggers: " . $conn->error);
}

// Now populate the tables with sample data from the original databases
// Only include if you want to prepopulate with the data from the original SQL dump

// For demonstration, let's include how to insert some data
// Uncomment these sections if you want to include sample data


// Insert courses
$sql = "INSERT INTO courses (course_name) VALUES
('DIPLOMA IN BUSINESS ACCOUNTING & FINANCE'),
('DIPLOMA IN ENTREPRENEURSHIP & MARKETING STRATEGIES'),
('DIPLOMA IN HUMAN CAPITAL MANAGEMENT'),
('DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS'),
('DIPLOMA IN APPLICATIONS DEVELOPMENT'),
('DIPLOMA IN CLOUD AND NETWORKING'),
('DIPLOMA IN DATA ANALYTICS'),
('DIGITAL ARTS AND MEDIA'),
('DIPLOMA IN WEB TECHNOLOGY'),
('DIPLOMA IN HEALTH SCIENCE (NURSING)'),
('DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)'),
('DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)'),
('DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)'),
('DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)'),
('DIPLOMA IN ARCHITECTURE'),
('DIPLOMA IN INTERIOR DESIGN'),
('DIPLOMA IN CIVIL ENGINEERING'),
('DIPLOMA IN ELECTRICAL ENGINEERING'),
('DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING'),
('DIPLOMA IN MECHANICAL ENGINEERING'),
('DIPLOMA IN PETROLEUM ENGINEERING')";

if ($conn->query($sql) === FALSE) {
    echo "Error inserting course data: " . $conn->error;
}

// Similar blocks for other tables' data


echo "Database and tables created successfully!";
?>