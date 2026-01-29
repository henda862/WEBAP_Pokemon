<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(array('success' => false, 'message' => 'User not authenticated'));
    exit;
}

require_once 'config.php';

$response = array('success' => false);

// Get username from session
$username = $_SESSION['user'];
$username = mysqli_real_escape_string($conn, substr($username, 0, 15));

// Insert or update user activity using INSERT ... ON DUPLICATE KEY UPDATE
$sql = "INSERT INTO users (username, last_active) 
        VALUES ('$username', NOW()) 
        ON DUPLICATE KEY UPDATE last_active = NOW()";

if (mysqli_query($conn, $sql)) {
    $response['success'] = true;
} else {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
}

mysqli_close($conn);
echo json_encode($response);
?>