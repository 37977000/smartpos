<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('header1.php');

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Check if user is logged in (assuming username is stored in session)
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Database connection
$host = "localhost"; // Changed from $servername to match your PDO connection
$username = "podata";
$password = "1234567";
$dbname = "pos";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Calculate total
    $calculated_total = array_sum(array_map(function($item) {
        return $item['selling_price'] * $item['quantity'];
    }, $_SESSION['cart']));
    
    // Get payment method and amount tendered from POST
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $amount_tendered = $_POST['amount_tendered'] ?? 0;

    // Validate amount tendered
    if ($amount_tendered < $calculated_total) {
        header("Location: cart.php?error=Amount tendered (₱".number_format($amount_tendered, 2).") is less than total amount (₱".number_format($calculated_total, 2).")");
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    // Create sale record with username
    $stmt = $conn->prepare("INSERT INTO sales 
                          (sale_date, total_amount, payment_method, amount_tendered, username) 
                          VALUES (NOW(), :total_amount, :payment_method, :amount_tendered, :username)");
    $stmt->bindParam(':total_amount', $calculated_total);
    $stmt->bindParam(':payment_method', $payment_method);
    $stmt->bindParam(':amount_tendered', $amount_tendered);
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->execute();
    $saleId = $conn->lastInsertId();
    
    // Add sale items and update inventory (unchanged)
    foreach ($_SESSION['cart'] as $item) {
        // Add sale item
        $stmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price) 
                               VALUES (:sale_id, :product_id, :quantity, :price)");
        $stmt->bindParam(':sale_id', $saleId);
        $stmt->bindParam(':product_id', $item['product_id']);
        $stmt->bindParam(':quantity', $item['quantity']);
        $stmt->bindParam(':price', $item['selling_price']);
        $stmt->execute();
        
        // Update inventory
        $stmt = $conn->prepare("UPDATE products SET quantity_received = quantity_received - :quantity 
                               WHERE id = :product_id");
        $stmt->bindParam(':quantity', $item['quantity']);
        $stmt->bindParam(':product_id', $item['product_id']);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect with success message
    header("Location: sales.php?message=Checkout successful. Sale ID: $saleId&change=".number_format($amount_tendered - $calculated_total, 2));
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollBack();
    }
    header("Location: cart.php?error=Checkout failed: " . $e->getMessage());
    exit();
}