<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
if (isset($_GET['id'], $_GET['name'], $_GET['pickup'])) {
  $_SESSION['pickup_client'] = $_GET['id'] . '|' . $_GET['name'];
}


// Display success message if any
if (isset($_GET['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['message']) . '</div>';
}

include('header1.php');

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$dbname = 'bamwai';
$username = 'csejay';
$password = '37977000';

// Handle pickup mode (adding to client tab)
if (isset($_GET['id'], $_GET['name'], $_GET['pickup'])) {
    $_SESSION['pickup_client'] = $_GET['id'] . '|' . $_GET['name'];
}

// Initialize variables
$categories = [];
$products = [];
$selectedCategory = '';
$message = '';
$alertThreshold = 100;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get stock alert threshold
    $thresholdStmt = $conn->query("SELECT limited FROM expiry ORDER BY id DESC LIMIT 1");
    $thresholdResult = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
    if ($thresholdResult) {
        $alertThreshold = $thresholdResult['limited'];
    }

    // Fetch categories
    $stmt = $conn->query("SELECT category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Handle search
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['search'])) {
            $selectedCategory = $_POST['category'];
            $sql = "SELECT id, product_name, quantity_received, selling_price, sold_by FROM products";
            if (!empty($selectedCategory)) {
                $sql .= " WHERE category_name = :category";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':category', $selectedCategory);
                $stmt->execute();
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $products = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            }
        } elseif (isset($_POST['view_all'])) {
            $products = $conn->query("SELECT id, product_name, quantity_received, selling_price, sold_by FROM products")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

// Show cart message if any
if (isset($_GET['message'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Product Search</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .stock-alert {
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      margin-right: 5px;
    }
    .alert-blink {
      background-color: orange;
      animation: blink 1s infinite;
    }
    .alert-solid {
      background-color: red;
    }
    .alert-success-text {
      color: green;
    }
    .alert-warning-text {
      color: orange;
    }
    .alert-danger-text {
      color: red;
    }
    @keyframes blink {
      50% { opacity: 0; }
    }
  </style>
</head>
<body>

<div class="container mt-3">
  <?php echo $message; ?>

  <!-- Pickup Mode Info -->
  <?php if (isset($_SESSION['pickup_client'])):
    list($pickupClientId, $pickupClientName) = explode('|', $_SESSION['pickup_client']);
  ?>
    <div class="alert alert-info mb-4">
      <strong>Adding items to: <?= htmlspecialchars($pickupClientName) ?> (Client ID: <?= htmlspecialchars($pickupClientId) ?>)</strong><br>
      These items will be added to the client's tab.
    </div>
  <?php endif; ?>

  <div class="cart-info mb-3">
    <a href="cart.php" class="btn btn-warning">
      View Cart
      <?php if (!empty($_SESSION['cart'])): ?>
        <span class="badge bg-danger"><?= array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>
      <?php endif; ?>
    </a>
  </div>

  <form method="POST" action="">
    <div class="form-container d-flex gap-2 mb-3">
      <select class="form-select" id="category" name="category">
        <option value="">Select a category</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= htmlspecialchars($category) ?>" <?= $category === $selectedCategory ? 'selected' : '' ?>>
            <?= htmlspecialchars($category) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-success" name="search">Search</button>
      <button type="submit" class="btn btn-info" name="view_all">View All</button>
    </div>
  </form>

  <div class="table-container">
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Available</th>
          <th>Mode of Sale</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Action</th>
          <th>Stock Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $product):
            $quantity = $product['quantity_received'];
            $isOutOfStock = $quantity <= 0;
            $isLowStock = $quantity > 0 && $quantity <= $alertThreshold;
            $modeOfSale = ucfirst($product['sold_by'] ?? 'Item');
            $isMeasurement = $product['sold_by'] === 'weight';
          ?>
            <tr>
              <td><?= htmlspecialchars($product['product_name']) ?></td>
              <td><?= $isMeasurement ? number_format($quantity, 2) : $quantity ?></td>
              <td><?= $modeOfSale ?></td>
              <td>Ksh<?= number_format($product['selling_price'], 2) ?></td>
              <td>
                <form method="POST" action="add_to_cart.php" class="d-flex">
                  <input type="number"
                         name="quantity"
                         value="<?= $isMeasurement ? '0.01' : '1' ?>"
                         min="<?= $isMeasurement ? '0.01' : '1' ?>"
                         step="<?= $isMeasurement ? '0.01' : '1' ?>"
                         max="<?= $quantity ?>"
                         class="form-control quantity-input"
                         <?= $isOutOfStock ? 'disabled' : '' ?>
                         required>
                  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                  <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">
                  <input type="hidden" name="sold_by" value="<?= htmlspecialchars($product['sold_by']) ?>">
                  <input type="hidden" name="selling_price" value="<?= $product['selling_price'] ?>">
                  <?php if (isset($pickupClientId, $pickupClientName)): ?>
                    <input type="hidden" name="client_id" value="<?= htmlspecialchars($pickupClientId) ?>">
                    <input type="hidden" name="client_name" value="<?= htmlspecialchars($pickupClientName) ?>">
                  <?php endif; ?>
              </td>
              <td>
                <button type="submit" class="btn btn-success btn-sm" <?= $isOutOfStock ? 'disabled' : '' ?>>Add to Cart</button>
                </form>
              </td>
              <td>
                <?php if ($isOutOfStock): ?>
                  <span class="stock-alert alert-solid"></span>
                  <span class="alert-danger-text">Out of Stock!</span>
                <?php elseif ($isLowStock): ?>
                  <span class="stock-alert alert-blink"></span>
                  <span class="alert-warning-text">Low Stock!</span>
                <?php else: ?>
                  <span class="stock-alert" style="background-color: #28a745;"></span>
                  <span class="alert-success-text">In Stock</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center">No products found. Use search or View All to display products.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
