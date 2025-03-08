<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /Website/authentication/login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $servername = "localhost";  
    $username = "root";         
    $password = "";             
    $dbname = "marketing_day";  

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = $_SESSION['user_id'];  
    
    // Process personal details
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ic_number = $_POST['ic_number'];
    
    // Handle profile picture upload
    $profile_pic_name = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($_FILES['profile_pic']['type'], $allowed_types) && $_FILES['profile_pic']['size'] <= $max_size) {
            // Create upload directory if it doesn't exist
            $upload_dir = "uploads/profile/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $profile_pic_name = time() . '_' . basename($_FILES['profile_pic']['name']);
            $profile_pic_path = $upload_dir . $profile_pic_name;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic_path)) {
                // Get and delete old profile pic if exists
                $sql = "SELECT profile_pic FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                
                if (!empty($user['profile_pic'])) {
                    $old_pic_path = $upload_dir . $user['profile_pic'];
                    if (file_exists($old_pic_path)) {
                        unlink($old_pic_path);
                    }
                }
            } else {
                $profile_pic_name = null;
            }
        }
    }
    
 // Handle CV upload or auto-generated CV
 $cv_name = null;
    
 // Check if we're auto-generating a CV
 if (isset($_POST['auto_generate_cv']) && $_POST['auto_generate_cv'] == '1' && isset($_POST['cv_content'])) {
     // Create upload directory if it doesn't exist
     $upload_dir = "uploads/cv/";
     if (!is_dir($upload_dir)) {
         mkdir($upload_dir, 0755, true);
     }
     
     // Get current CV filename to delete old file if exists
     $sql = "SELECT cv FROM users WHERE id = ?";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param("i", $user_id);
     $stmt->execute();
     $result = $stmt->get_result();
     $user_data = $result->fetch_assoc();
     $stmt->close();
     
     if (!empty($user_data['cv'])) {
         $old_cv_path = $upload_dir . $user_data['cv'];
         if (file_exists($old_cv_path)) {
             unlink($old_cv_path);
         }
     }
     
     // Generate unique filename for the HTML CV using user ID
     $cv_name = 'user_' . $user_id . '_CV_' . time() . '.html';
     $cv_path = $upload_dir . $cv_name;
     
     // Create HTML file with proper formatting
     $cv_html = '<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>CV - ' . htmlspecialchars($name) . '</title>
 <style>
     body {
         font-family: Arial, sans-serif;
         line-height: 1.6;
         margin: 0;
         padding: 20px;
         color: #333;
     }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3, h4 {
            color: #2c3e50;
        }
        h1 {
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h3 {
            border-bottom: 1px solid #3498db;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        .cv-item {
            margin-bottom: 20px;
        }
        .cv-item h4 {
            margin-bottom: 5px;
        }
        .contact-info {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        ' . $_POST['cv_content'] . '
    </div>
</body>
</html>';
        
        // Write HTML to file
        file_put_contents($cv_path, $cv_html);
        
    } elseif (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        // Manual CV upload
        $allowed_types = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (in_array($_FILES['cv']['type'], $allowed_types) && $_FILES['cv']['size'] <= $max_size) {
            // Create upload directory if it doesn't exist
            $upload_dir = "uploads/cv/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $cv_name = time() . '_' . basename($_FILES['cv']['name']);
            $cv_path = $upload_dir . $cv_name;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['cv']['tmp_name'], $cv_path)) {
                // Get and delete old CV if exists
                $sql = "SELECT cv FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                
                if (!empty($user['cv'])) {
                    $old_cv_path = $upload_dir . $user['cv'];
                    if (file_exists($old_cv_path)) {
                        unlink($old_cv_path);
                    }
                }
            } else {
                $cv_name = null;
            }
        }
    }
    
    // Update user's personal details
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, ic_number = ?";
    $params = [$name, $email, $phone, $ic_number];
    $types = "ssss";
    
    if ($profile_pic_name) {
        $sql .= ", profile_pic = ?";
        $params[] = $profile_pic_name;
        $types .= "s";
    }
    
    if ($cv_name) {
        $sql .= ", cv = ?";
        $params[] = $cv_name;
        $types .= "s";
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $user_id;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
    
    // Handle education background
    if (isset($_POST['education'])) {
        // First, delete all existing education records for this user
        $sql = "DELETE FROM education WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Then insert the new education records
        $education_data = $_POST['education'];
        
        foreach ($education_data as $index => $edu) {
            if (!empty($edu['education_level']) && !empty($edu['institution']) && 
                !empty($edu['field_of_study']) && !empty($edu['graduation_year'])) {
                
                // Handle certificate upload
                $certificate_name = null;
                if (isset($_FILES['education']) && isset($_FILES['education']['name'][$index]['certificate']) && 
                    $_FILES['education']['error'][$index]['certificate'] == 0) {
                    
                    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                    $file_type = $_FILES['education']['type'][$index]['certificate'];
                    $file_size = $_FILES['education']['size'][$index]['certificate'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if ((in_array($file_type, $allowed_types) || strpos($file_type, 'pdf') !== false) && $file_size <= $max_size) {
                        // Create upload directory if it doesn't exist
                        $upload_dir = "uploads/certificates/";
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $certificate_name = time() . '_edu_' . $index . '_' . basename($_FILES['education']['name'][$index]['certificate']);
                        $certificate_path = $upload_dir . $certificate_name;
                        
                        // Move uploaded file
                        move_uploaded_file($_FILES['education']['tmp_name'][$index]['certificate'], $certificate_path);
                    }
                }
                
                $sql = "INSERT INTO education (user_id, education_level, institution, field_of_study, graduation_year, certificate) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssis", $user_id, $edu['education_level'], $edu['institution'], 
                                $edu['field_of_study'], $edu['graduation_year'], $certificate_name);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // Handle achievements
    if (isset($_POST['achievements'])) {
        // First, delete all existing achievement records for this user
        $sql = "DELETE FROM achievements WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Then insert the new achievement records
        $achievement_data = $_POST['achievements'];
        
        foreach ($achievement_data as $index => $ach) {
            if (!empty($ach['title']) && !empty($ach['description']) && !empty($ach['year'])) {
                
                // Handle certificate upload
                $certificate_name = null;
                if (isset($_FILES['achievements']) && isset($_FILES['achievements']['name'][$index]['certificate']) && 
                    $_FILES['achievements']['error'][$index]['certificate'] == 0) {
                    
                    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                    $file_type = $_FILES['achievements']['type'][$index]['certificate'];
                    $file_size = $_FILES['achievements']['size'][$index]['certificate'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    if ((in_array($file_type, $allowed_types) || strpos($file_type, 'pdf') !== false) && $file_size <= $max_size) {
                        // Create upload directory if it doesn't exist
                        $upload_dir = "uploads/certificates/";
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        
                        // Generate unique filename
                        $certificate_name = time() . '_ach_' . $index . '_' . basename($_FILES['achievements']['name'][$index]['certificate']);
                        $certificate_path = $upload_dir . $certificate_name;
                        
                        // Move uploaded file
                        move_uploaded_file($_FILES['achievements']['tmp_name'][$index]['certificate'], $certificate_path);
                    }
                }
                
                $sql = "INSERT INTO achievements (user_id, title, description, year, certificate) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issis", $user_id, $ach['title'], $ach['description'], $ach['year'], $certificate_name);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    // Handle work experience
    if (isset($_POST['work'])) {
        // First, delete all existing work records for this user
        $sql = "DELETE FROM work_experience WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Then insert the new work records
        $work_data = $_POST['work'];
        
        foreach ($work_data as $work) {
            if (!empty($work['company']) && !empty($work['position']) && 
                !empty($work['start_date']) && !empty($work['description'])) {
                
                $end_date = !empty($work['end_date']) ? $work['end_date'] : null;
                
                $sql = "INSERT INTO work_experience (user_id, company, position, start_date, end_date, description) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss", $user_id, $work['company'], $work['position'], 
                                $work['start_date'], $end_date, $work['description']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    $conn->close();
    
    // Redirect back to profile page
    header("Location: profile.php?updated=1");
    exit();
}
?>
