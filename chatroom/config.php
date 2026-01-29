<?php
// Database configuration
$db_host = 'localhost';
$db_user = '';
$db_pass = '';
$db_name = '';

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");
?>