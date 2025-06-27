<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include('header1.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'bamwai';
$username = 'csejay';
$password = '37977000';

$categories = [];
$products = [];
$selectedCategory = '';
$message = '';
$alertThreshold = 100;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get alert threshold setting
    $thresholdStmt = $conn->query("SELECT limited FROM expiry ORDER BY id DESC LIMIT 1");
    $thresholdResult = $thresholdStmt->fetch(PDO::FETCH_ASSOC);
    if ($thresholdResult) {
        $alertThreshold = $thresholdResult['limited'];
    }

    // Get all categories
    $stmt = $conn->query("SELECT category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Handle search or view all requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['search'])) {
            $selectedCategory = $_POST['category'] ?? '';
            $sql = "SELECT id, product_name, quantity_received, selling_price, sold_by, barcode FROM products";
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
            $products = $conn->query("SELECT id, product_name, quantity_received, selling_price, sold_by, barcode FROM products")->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Display success message if present
if (isset($_GET['message'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_GET['message']) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Search</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .form-select {
            flex-grow: 1;
            min-width: 200px;
        }
        .quantity-input {
            width: 100px;
        }
        .cart-info {
            margin-bottom: 20px;
        }
        .badge {
            font-size: 0.9em;
        }
        @media (max-width: 768px) {
            .form-container {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
<div class="container mt-3">
    <?= $message ?>

    <div class="cart-info d-flex justify-content-between align-items-center">
        <a href="cart.php" class="btn btn-warning position-relative">
            View Cart
            <?php if (!empty($_SESSION['cart'])): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= array_sum(array_column($_SESSION['cart'], 'quantity')) ?>
                </span>
            <?php endif; ?>
        </a>
        <div class="text-muted">Threshold: <?= $alertThreshold ?> units</div>
    </div>

    <form method="POST" action="" class="mb-4">
        <div class="form-container">
            <select class="form-select" name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>" <?= $category === $selectedCategory ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success" name="search">Search</button>
                <button type="submit" class="btn btn-info" name="view_all">View All</button>
            </div>
        </div>
    </form>

    <?php if (!empty($products)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-dark">
                <tr>
                    <th>Item Name</th>
                    <th>Available</th>
                    <th>Mode of Sale</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Action</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): 
                    $quantity = (float)$product['quantity_received'];
                    $isOutOfStock = $quantity <= 0;
                    $isLowStock = $quantity > 0 && $quantity <= $alertThreshold;
                    $soldBy = $product['sold_by'] ?? 'one';
                    $modeOfSale = ucfirst($soldBy);
                    $isMeasurement = $soldBy === 'weight';
                    $price = number_format((float)$product['selling_price'], 2);
                ?>
                    <tr class="<?= $isOutOfStock ? 'table-secondary' : '' ?>">
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= $isMeasurement ? number_format($quantity, 2) : (int)$quantity ?></td>
                        <td><?= $modeOfSale ?></td>
                        <td>Ksh <?= $price ?></td>
                        <td>
                            <form method="POST" action="add_to_cart.php" class="d-flex gap-2">
                                <input type="number"
                                       name="quantity"
                                       value="<?= $isMeasurement ? '0.01' : '1' ?>"
                                       min="<?= $isMeasurement ? '0.01' : '1' ?>"
                                       step="<?= $isMeasurement ? '0.01' : '1' ?>"
                                       max="<?= $quantity ?>"
                                       class="form-control quantity-input"
                                       <?= $isOutOfStock ? 'disabled' : '' ?>
                                       required>
                                <button type="submit" class="btn btn-primary" <?= $isOutOfStock ? 'disabled' : '' ?>>
                                    Add
                                </button>
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">
                                <input type="hidden" name="selling_price" value="<?= $product['selling_price'] ?>">
                                <input type="hidden" name="sold_by" value="<?= htmlspecialchars($soldBy) ?>">
                            </form>
                        </td>
                        <td class="align-middle">
                            <?php if ($isOutOfStock): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php elseif ($isLowStock): ?>
                                <span class="badge bg-warning text-dark">Low Stock</span>
                            <?php else: ?>
                                <span class="badge bg-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-3">No products found. Try a different search.</div>
    <?php endif; ?>
</div>

<!-- Barcode scanner implementation -->
<input type="text" id="barcode-scanner" class="visually-hidden" autofocus>

<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const scannerInput = document.getElementById('barcode-scanner');
    let barcodeBuffer = '';
    let lastScanTime = 0;
    
    // Improved barcode scanner handling
    scannerInput.addEventListener('keydown', (e) => {
        const currentTime = Date.now();
        
        // Reset buffer if too much time passed between keystrokes
        if (currentTime - lastScanTime > 100) {
            barcodeBuffer = '';
        }
        lastScanTime = currentTime;
        
        // Only process alphanumeric characters
        if (e.key.length === 1 && /[a-zA-Z0-9]/.test(e.key)) {
            barcodeBuffer += e.key;
        }
        
        // Process when Enter is pressed
        if (e.key === 'Enter' && barcodeBuffer.length > 3) {
            window.location.href = `add_to_cart_from_barcode.php?barcode=${encodeURIComponent(barcodeBuffer)}`;
            barcodeBuffer = '';
            e.preventDefault();
        }
    });
    
    // Maintain focus on scanner input
    setInterval(() => {
        if (document.activeElement !== scannerInput) {
            scannerInput.focus();
        }
    }, 200);
});
</script>
</body>
</html>