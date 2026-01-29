<?php
// Debug file to test database connection and operations
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Database Debug Test</h1>";

// Database configuration
$db_host = 'localhost';
$db_user = 'henda862sql9';
$db_pass = 'Nokia@2019+';
$db_name = 'henda862sql9';

echo "<h2>1. Testing Connection</h2>";

// Create connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("<p style='color:red;'>Connection failed: " . mysqli_connect_error() . "</p>");
}

echo "<p style='color:green;'>Connection successful!</p>";

// Set charset
mysqli_set_charset($conn, "utf8");

// Test 2: Check tables exist
echo "<h2>2. Checking Tables</h2>";

$tables = array('messages', 'users');
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color:green;'>Table '$table' exists</p>";
    } else {
        echo "<p style='color:red;'>Table '$table' does NOT exist!</p>";
    }
}

// Test 3: Show table structures
echo "<h2>3. Table Structures</h2>";

echo "<h3>Messages Table:</h3>";
$result = mysqli_query($conn, "DESCRIBE messages");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Users Table:</h3>";
$result = mysqli_query($conn, "DESCRIBE users");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
}

// Test 4: Try to insert a test message
echo "<h2>4. Test Insert into Messages</h2>";

$testName = 'TestUser';
$testContent = 'Test message at ' . date('Y-m-d H:i:s');

$sql = "INSERT INTO messages (name, content, recipient) VALUES ('$testName', '$testContent', NULL)";
echo "<p>SQL: <code>$sql</code></p>";

if (mysqli_query($conn, $sql)) {
    $insertId = mysqli_insert_id($conn);
    echo "<p style='color:green;'>Message inserted successfully! ID: $insertId</p>";
} else {
    echo "<p style='color:red;'>Error inserting message: " . mysqli_error($conn) . "</p>";
}

// Test 5: Try to insert/update a test user
echo "<h2>5. Test Insert into Users</h2>";

$testUsername = 'TestUser';

$sql = "INSERT INTO users (username, last_active) VALUES ('$testUsername', NOW()) ON DUPLICATE KEY UPDATE last_active = NOW()";
echo "<p>SQL: <code>$sql</code></p>";

if (mysqli_query($conn, $sql)) {
    $affectedRows = mysqli_affected_rows($conn);
    echo "<p style='color:green;'>User insert/update successful! Affected rows: $affectedRows</p>";
} else {
    echo "<p style='color:red;'>Error inserting/updating user: " . mysqli_error($conn) . "</p>";
}

// Test 6: Show current data
echo "<h2>6. Current Data in Tables</h2>";

echo "<h3>Messages (last 10):</h3>";
$result = mysqli_query($conn, "SELECT * FROM messages ORDER BY id DESC LIMIT 10");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Time</th><th>Name</th><th>Content</th><th>Recipient</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['time'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . htmlspecialchars($row['content']) . "</td>";
        echo "<td>" . ($row['recipient'] ? $row['recipient'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No messages found or error: " . mysqli_error($conn) . "</p>";
}

echo "<h3>Users:</h3>";
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY last_active DESC LIMIT 10");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Last Active</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['last_active'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found or error: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

echo "<hr><p>Debug complete.</p>";
?>