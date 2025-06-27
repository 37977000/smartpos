<?php
session_start();



include ('header1.php');
// Database connection details
$host = 'localhost'; // Replace with your host
$dbname = 'bamwai'; // Replace with your database name
$username = 'csejay'; // Replace with your database username
$password = '37977000'; // Replace with your database password

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch (PDOException $e) {
    // Handle database errors
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>
