<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politeknik Brunei Marketing Day - Jobs</title>
    <link rel="stylesheet" href="/Website/assets/css/index.css">
    <link rel="stylesheet" href="/Website/assets/css/job_details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php
        session_start();
        // Fix the path to the database connection file
        include_once 'D:/xampp/htdocs/Website/includes/db.php';
        
        // If the database connection file doesn't exist or $conn is not defined, create a connection
        if (!isset($conn)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "marketing_day";
            
            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);
            
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
        }
    ?>
</head>