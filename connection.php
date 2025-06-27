<?php
$servername = "localhost"; // Change if necessary
$username = "siele"; // Change if necessary
$password = "37977000"; // Change if necessary
$dbname = "siele"; // Change if necessary

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
