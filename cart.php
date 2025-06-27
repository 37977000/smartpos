<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include('header1.php');

// Initialize $message at the very beginning
$message = '';

// Debug session
error_log("Cart Page - Session ID: " . session_id());
error_log("Cart Contents: " . print_r($_SESSION['cart'] ?? 'Empty', true));

// Handle remove item action
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $productId) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        // Reset array keys only if cart is not empty
        if (!empty($_SESSION['cart'])) {
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        header("Location: cart.php?message=Item removed from cart");
        exit();
    }
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_POST['quantity'] as $productId => $quantity) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] == $productId) {
                    $item['quantity'] = max(1, (int)$quantity); // Ensure quantity is at least 1
                    break;
                }
            }
        }
        header("Location: cart.php?message=Cart updated");
        exit();
    }
}

// Process messages after potential redirects
if (isset($_GET['message'])) {
    $message = '<div class="alert alert-success">'.htmlspecialchars($_GET['message']).'</div>';
}
if (isset($_GET['error'])) {
    $message = '<div class="alert alert-danger">'.htmlspecialchars($_GET['error']).'</div>';
}

// Calculate total
$total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['selling_price'] * $item['quantity'];
    }
}
$total = round($total, 2); // Ensure exactly 2 decimal places
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .cart-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
    }
    .quantity-input {
      width: 70px !important;
      text-align: center;
    }
    .amount-input {
      width: 150px !important;
      text-align: right;
    }
    .table {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="cart-container">
    <h2 class="text-center mb-4">Your Shopping Cart</h2>
    <?php echo $message; ?>
    
    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
      <div class="alert alert-info text-center">Your cart is empty.</div>
    <?php else: ?>
      <form method="POST" action="cart.php" id="cartForm">
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th style="width: 100px;">Quantity</th>
              <th>Subtotal</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($_SESSION['cart'] as $item): 
              $subtotal = round($item['selling_price'] * $item['quantity'], 2);
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td>₱<?= number_format($item['selling_price'], 2) ?></td>
                <td>
                  <input type="number" name="quantity[<?= $item['product_id'] ?>]" 
                         value="<?= $item['quantity'] ?>" min="1" class="form-control quantity-input">
                </td>
                <td>₱<?= number_format($subtotal, 2) ?></td>
                <td>
                  <a href="cart.php?remove=<?= $item['product_id'] ?>" class="btn btn-danger btn-sm">Remove</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr>
              <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
              <td><strong>₱<?= number_format($total, 2) ?></strong></td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end"><strong>Mode of Payment:</strong></td>
              <td>
                <select class="form-select" name="payment_method" required>
                  <option value="cash">Cash</option>
                  <option value="gcash">GCash</option>
                  <option value="credit_card">Credit Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                </select>
              </td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end"><strong>Total:</strong></td>
              <td><strong>₱<?= number_format($total, 2) ?></strong></td>
              <td></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end"><strong>Amount Tendered:</strong></td>
              <td>
                <input type="number" name="amount_tendered" 
                       class="form-control amount-input" 
                       min="<?= number_format($total, 2, '.', '') ?>" 
                       step="0.01" 
                       value="<?= number_format($total, 2, '.', '') ?>" 
                       required>
              </td>
              <td></td>
            </tr>
          </tbody>
        </table>
        <div class="d-flex justify-content-between">
          <a href="sales.php" class="btn btn-primary">Continue Shopping</a>
          <div>
            <button type="submit" name="update_quantity" class="btn btn-warning">Update Cart</button>
            <button type="submit" form="cartForm" formaction="checkout.php" class="btn btn-success">Checkout</button>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
  
  <script>
    // Client-side validation for amount tendered
    document.getElementById('cartForm').addEventListener('submit', function(e) {
      const amountTendered = parseFloat(document.querySelector('input[name="amount_tendered"]').value);
      const total = parseFloat(<?= $total ?>);
      
      if (amountTendered < total) {
        e.preventDefault();
        alert('Amount tendered must be equal to or greater than the total amount');
      }
    });
  </script>
</body>
</html>