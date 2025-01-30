<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'error' => 'Not authenticated']));
}

if (isset($_FILES['image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $user_id = $_SESSION['user_id'];
        $sql = "UPDATE users SET profile_image = '$target_file' WHERE id = $user_id";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
    }
}
?>