<?php
// add_to_cart.php

session_start();

// Get product data from POST
$product = json_decode(file_get_contents('php://input'), true);

if ($product) {
    // Initialize cart if not already
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart
    $productId = $product['id'];
    if (isset($_SESSION['cart'][$productId])) {
        // Update quantity if product exists
        $_SESSION['cart'][$productId]['quantity'] += $product['quantity'];
    } else {
        // Add new product to cart
        $_SESSION['cart'][$productId] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $product['quantity']
        ];
    }

    // Return success response
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
