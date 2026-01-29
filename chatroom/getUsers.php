<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(array('success' => false, 'message' => 'User not authenticated'));
    exit;
}

require_once 'config.php';

$response = array('success' => false, 'users' => array());

// Get users who were active in the last 30 seconds
$sql = "SELECT username FROM users 
        WHERE last_active > DATE_SUB(NOW(), INTERVAL 30 SECOND) 
        ORDER BY username ASC";

$result = mysqli_query($conn, $sql);

if ($result) {
    $response['success'] = true;
    while ($row = mysqli_fetch_assoc($result)) {
        $response['users'][] = $row['username'];
    }
    mysqli_free_result($result);
} else {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
}

mysqli_close($conn);
echo json_encode($response);
?>