<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
require_once 'connection.php'; // Include your database connection file

$products = [];
$alertThreshold = 100; // Set your low quantity alert threshold here

try {
    // Query to get all products with their available quantity
    $query = $pdo->prepare("
        SELECT 
            id,
            product_name,
            quantity_received AS quantity_available,
            buying_price,
            selling_price,
            category_name,
            expiry_date
        FROM products
        ORDER BY product_name ASC
    ");
    $query->execute();
    $products = $query->fetchAll();

} catch (PDOException $e) {
    $errorMessage = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Inventory Alert</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .inventory-container {
            padding: 20px;
            margin-top: 20px;
        }
        .low-stock {
            background-color: #fff3cd !important; /* Light yellow for warning */
        }
        .critical-stock {
            background-color: #f8d7da !important; /* Light red for critical */
        }
        .alert-cell {
            font-weight: bold;
        }
        .alert-low {
            color: #ffc107; /* Yellow for low stock */
        }
        .alert-critical {
            color: #dc3545; /* Red for critical stock */
        }
        .alert-good {
            color: #28a745; /* Green for good stock */
        }
        .table thead th {
            background-color: #343a40;
            color: white;
        }
        .blinking-btn {
            animation: blink 1s linear infinite;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }
        .action-col {
            width: 100px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container inventory-container">
        <h1 class="text-center mb-4">Product Inventory Status</h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Product Stock Levels</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Quantity Available</th>
                                <th>Buying Price</th>
                                <th>Selling Price</th>
                                <th>Expiry Date</th>
                                <th>Stock Alert</th>
                                <th class="action-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $index => $product): 
                                // Determine alert status
                                $quantity = $product['quantity_available'];
                                $alertClass = '';
                                $alertText = '';
                                $rowClass = '';
                                
                                if ($quantity <= 0) {
                                    $alertClass = 'alert-critical';
                                    $alertText = 'OUT OF STOCK';
                                    $rowClass = 'critical-stock';
                                } elseif ($quantity <= $alertThreshold) {
                                    $alertClass = 'alert-low';
                                    $alertText = 'LOW STOCK';
                                    $rowClass = 'low-stock';
                                } else {
                                    $alertClass = 'alert-good';
                                    $alertText = 'In Stock';
                                }
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($product['product_name']) ?></td>
                                <td><?= htmlspecialchars($product['category_name']) ?></td>
                                <td><?= htmlspecialchars($quantity) ?></td>
                                <td><?= number_format($product['buying_price'], 2) ?></td>
                                <td><?= number_format($product['selling_price'], 2) ?></td>
                                <td><?= htmlspecialchars($product['expiry_date']) ?></td>
                                <td class="alert-cell <?= $alertClass ?>">
                                    <?= $alertText ?>
                                    <?php if ($quantity <= $alertThreshold && $quantity > 0): ?>
                                        (<?= $quantity ?> left)
                                    <?php endif; ?>
                                </td>
                                <td class="action-col">
                                    <?php if ($quantity <= $alertThreshold): ?>
                                        <button class="btn btn-danger blinking-btn" title="Low stock alert!">
                                            !
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary rounded-circle" disabled style="width: 30px; height: 30px;">
                                            ✓
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    <span class="badge bg-success">In Stock</span>
                    <span class="badge bg-warning text-dark">Low Stock (≤ <?= $alertThreshold ?>)</span>
                    <span class="badge bg-danger">Out of Stock</span>
                    <span class="badge bg-danger blinking-btn">!</span> Blinking alert for low stock
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>