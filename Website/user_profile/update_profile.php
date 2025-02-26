<?php
// Database connection
$servername = "localhost";  // Database server
$username = "root";         // Database username
$password = "";             // Database password
$dbname = "marketing_day";  // Database name

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

    // **Check if the uploads directory exists, if not, create it**
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);  // Creates the directory with full permissions
    }

    // Generate a unique file name using the user ID or name
    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $new_file_name = "profile_" . $user_id . "." . $file_extension;  // Example: profile_1.jpg
    $target_file = $target_dir . $new_file_name;

    // Delete the old profile picture if it exists
    if (!empty($user['profile_pic'])) {
        $old_file = $target_dir . $user['profile_pic'];
        if (file_exists($old_file)) {
            unlink($old_file);  // Delete the old file
        }
    }

    // Debugging messages
    echo "Temp file: " . $_FILES['profile_pic']['tmp_name'] . "<br>";
    echo "Target file: " . $target_file . "<br>";

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        echo "File uploaded successfully!<br>";
        $profile_pic = $new_file_name; // Store the new file name in the database
    } else {
        echo "❌ Error moving file. Check folder permissions.<br>";
        echo "Error Code: " . $_FILES['profile_pic']['error'] . "<br>";
    }
} else {
    if (isset($_FILES['profile_pic'])) {
        echo "❌ Error during file upload. Error Code: " . $_FILES['profile_pic']['error'] . "<br>";
    }
}

// Update the database
$sql = "UPDATE users SET name = ?, email = ?, phone = ?";
$params = [$name, $email, $phone]; // Store parameters in an array

if (!empty($profile_pic)) {
    $sql .= ", profile_pic = ?";
    $params[] = $profile_pic; // Add profile_pic to the parameters array
}
$sql .= " WHERE id = ?";
$params[] = $user_id; // Add user_id to the parameters array

// Prepare and bind parameters dynamically
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

if ($stmt->execute()) {
    echo "<script>
        alert('Profile updated successfully!');
        window.location.href = 'profile.php';
    </script>";
} else {
    echo "❌ SQL Error: " . $conn->error . "<br>";
}

// Close the database connection
$stmt->close();
$conn->close();
?>
