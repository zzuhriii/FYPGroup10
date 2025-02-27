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
$profile_pic = $_POST['pfp'];

// Fetch the existing profile picture name from the database
$sql = "SELECT profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// File upload logic
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "uploads/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $new_file_name = "profile_" . $user_id . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if (!empty($user['profile_pic'])) {
        $old_file = $target_dir . $user['profile_pic'];
        if (file_exists($old_file)) {
            unlink($old_file);
        }
    }

    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        $profile_pic = $new_file_name;
    }
}

// Update the database
$sql = "UPDATE users SET name = ?, email = ?, phone = ?, ic_number = ?";
$params = [$name, $email, $phone, $ic_number];

if (!empty($profile_pic)) {
    $sql .= ", profile_pic = ?";
    $params[] = $profile_pic;
}
$sql .= " WHERE id = ?";
$params[] = $user_id;

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
