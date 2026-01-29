<?php
// Check if the POST parameters are set
if (
    isset($_POST['username']) &&
    isset($_POST['birthdate']) &&
    isset($_POST['gender']) &&
    isset($_POST['hometown']) &&
    isset($_POST['password']) &&
    isset($_POST['passwordc'])
) {
    // Retrieve POST data
    $username = $_POST['username'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $hometown = $_POST['hometown'];
    $password = $_POST['password'];
    $passwordc = $_POST['passwordc'];

    // Check if passwords match
    if ($password !== $passwordc) {
        echo "Passwords do not match.";
        http_response_code(400); // Bad Request
        exit;
    }

    // Include database connection details
    require_once("db_credentials.php");

    // Create a new MySQLi connection
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME);

    // Check if the connection was successful
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare an SQL INSERT statement
    $stmt = $mysqli->prepare("INSERT INTO trainers (username, birthdate, gender, hometown, password) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        http_response_code(500); // Internal Server Error
        exit;
    }

    // Bind the parameters to the statement
    $stmt->bind_param("sssss", $username, $birthdate, $gender, $hometown, $hashedPassword);

    // Execute the statement
    if ($stmt->execute()) {
        // Close the statement and database connection
        $stmt->close();
        $mysqli->close();

        // Redirect to login page after successful registration
        header("Location: ../index.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
        http_response_code(500); // Internal Server Error
    }

    // Close the statement and database connection
    $stmt->close();
    $mysqli->close();
} else {
    echo "Incomplete POST data.";
    http_response_code(400); // Bad Request
}
?>