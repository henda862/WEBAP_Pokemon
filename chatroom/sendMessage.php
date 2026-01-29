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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get username from session
    $name = $_SESSION['user'];
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $recipient = isset($_POST['recipient']) ? trim($_POST['recipient']) : '';

    // Validate inputs
    if (empty($content)) {
        $response['message'] = 'Message content is required';
        echo json_encode($response);
        exit;
    }

    // Sanitize and limit length
    $name = mysqli_real_escape_string($conn, substr($name, 0, 15));
    $content = mysqli_real_escape_string($conn, substr($content, 0, 300));

    // Handle recipient (NULL for broadcast, username for private message)
    if (empty($recipient)) {
        $recipientValue = "NULL";
    } else {
        $recipient = mysqli_real_escape_string($conn, substr($recipient, 0, 15));
        $recipientValue = "'$recipient'";
    }

    // Insert the message
    $sql = "INSERT INTO messages (name, content, recipient) 
            VALUES ('$name', '$content', $recipientValue)";

    if (mysqli_query($conn, $sql)) {
        $response['success'] = true;
        $response['message'] = 'Message sent successfully';
        $response['id'] = mysqli_insert_id($conn);
    } else {
        $response['message'] = 'Database error: ' . mysqli_error($conn);
    }
} else {
    $response['message'] = 'Invalid request method';
}

mysqli_close($conn);
echo json_encode($response);
?>