<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(array('success' => false, 'message' => 'User not authenticated'));
    exit;
}

require_once 'config.php';

$response = array('success' => false, 'messages' => array());

// Get current username from session
$username = $_SESSION['user'];
$username = mysqli_real_escape_string($conn, $username);

// Build query to get messages:
// - All broadcast messages (recipient IS NULL)
// - Private messages where user is sender OR recipient
$sql = "SELECT id, name, content, recipient,
        DATE_FORMAT(time, '%Y-%m-%d %H:%i:%s') as time 
        FROM messages 
        WHERE recipient IS NULL 
           OR recipient = '$username' 
           OR name = '$username'
        ORDER BY time ASC, id ASC";

$result = mysqli_query($conn, $sql);

if ($result) {
    $response['success'] = true;
    while ($row = mysqli_fetch_assoc($result)) {
        $response['messages'][] = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'content' => $row['content'],
            'recipient' => $row['recipient'],
            'time' => $row['time']
        );
    }
    mysqli_free_result($result);
} else {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
}

mysqli_close($conn);
echo json_encode($response);
?>