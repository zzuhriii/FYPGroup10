<?php
// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "marketing_day";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE 'company_profile'");
if ($table_check->num_rows > 0) {
    // Table exists, check if user_id column exists
    $column_check = $conn->query("SHOW COLUMNS FROM company_profile LIKE 'user_id'");
    if ($column_check->num_rows == 0) {
        // Add user_id column if it doesn't exist
        $alter_sql = "ALTER TABLE company_profile ADD COLUMN user_id INT(11) AFTER id";
        if ($conn->query($alter_sql) === TRUE) {
            echo "Added user_id column to existing company_profile table<br>";
        } else {
            echo "Error adding user_id column: " . $conn->error . "<br>";
        }
    } else {
        echo "user_id column already exists<br>";
    }
} else {
    // Create table with user_id column
    $sql = "CREATE TABLE IF NOT EXISTS company_profile (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11),
        company_name VARCHAR(255),
        tagline VARCHAR(255),
        location VARCHAR(255),
        contact_info TEXT,
        founding_date VARCHAR(100),
        founders TEXT,
        milestones TEXT,
        mission TEXT,
        vision TEXT,
        products TEXT,
        usp TEXT,
        awards TEXT,
        testimonials TEXT,
        about_us TEXT,
        logo MEDIUMBLOB,
        office_photo MEDIUMBLOB,
        infographic MEDIUMBLOB,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Table company_profile created successfully<br>";
        
        // Insert a default empty record
        $insertSql = "INSERT INTO company_profile (company_name) VALUES ('Your Company Name')";
        if ($conn->query($insertSql) === TRUE) {
            echo "Default record created successfully<br>";
        } else {
            echo "Error creating default record: " . $conn->error . "<br>";
        }
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

$conn->close();

echo "<a href='CompanyProfile.php'>Go to Company Profile</a>";

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

?>