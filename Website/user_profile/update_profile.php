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
$profile_pic = '';

// Fetch the existing profile picture name from the database
$sql = "SELECT profile_pic FROM users WHERE id = '$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// File upload logic
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "uploads/";

    // Generate a unique file name using the user ID or name
    $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $new_file_name = "profile_" . $user_id . "." . $file_extension;  // For example: profile_1.jpg

    $target_file = $target_dir . $new_file_name;

    // Delete the old profile picture if it exists
    if ($user['profile_pic']) {
        $old_file = $target_dir . $user['profile_pic'];
        if (file_exists($old_file)) {
            unlink($old_file);  // Delete the old file
        }
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
        echo "The file " . htmlspecialchars($new_file_name) . " has been uploaded successfully.";
        $profile_pic = $new_file_name; // Store the new file name in the database
    } else {
        echo "Sorry, there was an error uploading your file.";
        echo "<br>Error Code: " . $_FILES['profile_pic']['error'];
    }
} else {
    if (isset($_FILES['profile_pic'])) {
        echo "Error during file upload. Error Code: " . $_FILES['profile_pic']['error'];
    }
}

// Update the database
$sql = "UPDATE users SET name = '$name', email = '$email', phone = '$phone'";

if ($profile_pic) {
    $sql .= ", profile_pic = '$profile_pic'";  // Only include profile_pic if it is uploaded
}

$sql .= " WHERE id = $user_id";

// Execute the query and check if it's successful
if ($conn->query($sql) === TRUE) {
    echo "Profile updated successfully!";
    header("Location: profile.php");  // Redirect back to the profile page
    exit();
} else {
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>
