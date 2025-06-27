<?php
// get_product_by_barcode.php

// Database connection
$host = 'localhost';
$dbname = 'bamwai';
$username = 'csejay';
$password = '37977000';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get barcode from query parameter
    $barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';

    if ($barcode) {
        // Prepare and execute query
        $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = :barcode LIMIT 1");
        $stmt->bindParam(':barcode', $barcode);
        $stmt->execute();

        // Fetch product details
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Return product details as JSON
            echo json_encode($product);
        } else {
            echo json_encode(null);
        }
    } else {
        echo json_encode(null);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
