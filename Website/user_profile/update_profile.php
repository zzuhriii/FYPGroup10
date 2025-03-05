<?php
// Database connection
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

// Retrieve the user data from the POST request
$user_id = $_POST['user_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$ic_number = $_POST['ic_number'];

// Fetch the existing user data (profile picture and CV) from the database
$sql = "SELECT profile_pic, cv FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize variables for the profile picture and CV
$profile_pic = $user['profile_pic'];
$cv = $user['cv'];

// File upload logic for profile picture
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "uploads/profile/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $new_profile_pic_name = "profile_" . $user_id . "." . $file_extension;
    $target_file = $target_dir . $new_profile_pic_name;

    // Delete the old profile picture if it exists
    if (!empty($user['profile_pic'])) {
        $old_file = $target_dir . $user['profile_pic'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        $profile_pic = $new_profile_pic_name;
    }
}

// File upload logic for CV
if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
    $target_dir = "uploads/cv/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION);
    $new_cv_name = "cv_" . $user_id . "_" . $ic_number . "." . $file_extension;
    $target_file = $target_dir . $new_cv_name;

    // Delete old CV if exists
    if (!empty($user['cv'])) {
        $old_cv = $target_dir . $user['cv'];
        if (file_exists($old_cv)) {
            unlink($old_cv);
        }
    }

    if (move_uploaded_file($_FILES['cv']['tmp_name'], $target_file)) {
        $cv = $new_cv_name;
    }
}

// Handle CV deletion
if (isset($_POST['delete_cv']) && $_POST['delete_cv'] == 1 && !empty($cv)) {
    $target_dir = "uploads/cv/";
    $old_cv = $target_dir . $cv;
    
    if (file_exists($old_cv)) {
        unlink($old_cv); // Delete the file
    }

    // Set the cv column to null
    $cv = null;
}

// Update the database with the new profile picture and CV
$sql = "UPDATE users SET name = ?, email = ?, phone = ?, ic_number = ?";

$params = [$name, $email, $phone, $ic_number];

if (!empty($profile_pic)) {
    $sql .= ", profile_pic = ?";
    $params[] = $profile_pic;
}

if (!empty($cv)) {
    $sql .= ", cv = ?";
    $params[] = $cv;
} else {
    $sql .= ", cv = NULL";
}

$sql .= " WHERE id = ?";
$params[] = $user_id;

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

if ($stmt->execute()) {
    echo "<script>
        alert('Profile updated successfully!');
        window.location.href = 'profile.php';
    </script>";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
