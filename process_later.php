<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update quantities first if needed
    if (isset($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $productId) {
                    $item['quantity'] = max(1, (int)$quantity);
                    break;
                }
            }
        }
    }

    // Store cart items in session for waiting.php
    $_SESSION['waiting_order'] = [
        'items' => $_SESSION['cart'],
        'total' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Calculate total
    foreach ($_SESSION['cart'] as $item) {
        $_SESSION['waiting_order']['total'] += $item['selling_price'] * $item['quantity'];
    }
    
    // Clear current cart
    unset($_SESSION['cart']);
    
    // Redirect to waiting.php
    header("Location: waiting.php");
    exit();
}

// If not POST, redirect back
header("Location: cart.php");
exit();
?>