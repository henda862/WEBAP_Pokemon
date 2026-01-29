<?php
// Start or resume the session
session_start();

// Check if the user is logged in (adjust this based on your authentication system)
if (!isset($_SESSION['id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(array("message" => "User not authenticated."));
    exit;
}

// Include database connection details
require_once("db_credentials.php");

// Create a new MySQLi connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME);

// Check if the connection was successful
if ($mysqli->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(array("message" => "Database connection error."));
    exit;
}

// Prepare an SQL SELECT statement to retrieve the trainer's Pokemons
// Retrieve the trainer's ID from the session variable "id"
$idTrainer = $_SESSION['id'];

// Query to get all Pokemon for this trainer with species info
$sql = "SELECT p.idPokemon, p.idSpecies, p.nickname, p.level, p.experience, p.health, s.name AS speciesName
        FROM pokemon p
        JOIN species s ON p.idSpecies = s.idSpecies
        WHERE p.idTrainer = ?";

$stmt = $mysqli->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(array("message" => "Prepare failed: " . $mysqli->error));
    exit;
}

$stmt->bind_param("i", $idTrainer);

if ($stmt->execute() === false) {
    http_response_code(500);
    echo json_encode(array("message" => "Query execution failed: " . $stmt->error));
    exit;
}

// Get the results
$result = $stmt->get_result();

// Fetch and format the data
$pokemon = array();
while ($row = $result->fetch_assoc()) {
    // Determine health class based on health value
    // Use classes: full-health above 70 hp, medium-health between 30-70 hp, low-health below 30
    if ($row['health'] > 70) {
        $healthClass = 'full-health';
    } else if ($row['health'] >= 30) {
        $healthClass = 'medium-health';
    } else {
        $healthClass = 'low-health';
    }

    // Format idSpecies with leading zeros for image path (e.g., 7 -> 007)
    $formattedSpeciesId = str_pad($row['idSpecies'], 3, '0', STR_PAD_LEFT);

    $pokemon[] = array(
        'idPokemon' => $row['idPokemon'],
        'idSpecies' => $row['idSpecies'],
        'formattedSpeciesId' => $formattedSpeciesId,
        'nickname' => $row['nickname'] ? $row['nickname'] : $row['speciesName'],
        'speciesName' => $row['speciesName'],
        'level' => $row['level'],
        'experience' => $row['experience'],
        'health' => $row['health'],
        'healthClass' => $healthClass
    );
}

// Close the statement and database connection
$stmt->close();
$mysqli->close();

// Set content type header
header('Content-Type: application/json');

// send JSON Response with all Pokemon if it worked!
echo json_encode(array("success" => true, "pokemon" => $pokemon));
?>