<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Verify the session is working
error_log("Session ID: " . session_id());

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $productId = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $productName = $_POST['product_name'];
    $sellingPrice = (float)$_POST['selling_price'];

    // Check if product already exists in cart
    $itemExists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $productId) {
            $item['quantity'] += $quantity;
            $itemExists = true;
            break;
        }
    }

    if (!$itemExists) {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'product_name' => $productName,
            'selling_price' => $sellingPrice,
            'quantity' => $quantity
        ];
    }

    // Debug: Log the cart contents
    error_log("Cart contents: " . print_r($_SESSION['cart'], true));

    header("Location: sales.php?message=Product added to cart");
    exit();
} else {
    header("Location: sales.php?error=Invalid request");
    exit();
}