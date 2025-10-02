<?php
// db.php - Database Connection File
$servername = "localhost"; // Assuming localhost, change if needed
$username = "uasxxqbztmxwm";
$password = "wss863wqyhal";
$dbname = "dbr8pkeituyfjf";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?>
