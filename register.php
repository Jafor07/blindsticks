<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        // Return JSON response for AJAX handling
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $conn->error
        ]);
    }

    $conn->close();
}
?>