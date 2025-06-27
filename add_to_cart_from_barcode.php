<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['barcode']) || empty(trim($_GET['barcode']))) {
    header("Location: sales1.php?message=" . urlencode("No barcode provided."));
    exit();
}

$barcode = trim($_GET['barcode']);

$host = 'localhost';
$dbname = 'bamwai';
$username = 'csejay';
$password = '37977000';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT id, product_name, quantity_received, selling_price, sold_by FROM products WHERE barcode = :barcode");
    $stmt->bindParam(':barcode', $barcode);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: sales1.php?message=" . urlencode("Product not found for barcode: $barcode"));
        exit();
    }

    // Determine quantity to add per scan
    $isMeasurement = $product['sold_by'] === 'weight';
    $incrementQty = $isMeasurement ? 0.01 : 1;

    if ($product['quantity_received'] <= 0) {
        header("Location: sales1.php?message=" . urlencode("Product '{$product['product_name']}' is out of stock."));
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $product['id']) {
            // Update quantity
            $item['quantity'] += $incrementQty;
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Add as new item
        $_SESSION['cart'][] = [
            'product_id' => $product['id'],
            'product_name' => $product['product_name'],
            'quantity' => $incrementQty,
            'selling_price' => $product['selling_price'],
            'barcode' => $barcode
        ];
    }

    header("Location: sales1.php?message=" . urlencode("Scanned '{$product['product_name']}' — quantity updated."));
    exit();

} catch (PDOException $e) {
    header("Location: sales1.php?message=" . urlencode("Database error: " . $e->getMessage()));
    exit();
}
