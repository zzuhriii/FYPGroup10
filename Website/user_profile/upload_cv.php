function uploadCV($conn, $userId, $file) {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($file["name"]);
    
    // Check file type
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if (!in_array($fileType, ["pdf", "docx"])) {
        return "Only PDF and DOCX files allowed.";
    }

    // Move file & save in database
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $sql = "INSERT INTO cvs (user_id, cv_file) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $targetFile);
        return $stmt->execute();
    }
    return "Upload failed.";
}
