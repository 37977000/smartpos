<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
require_once 'connection.php'; // Include your database connection file

$expiredItems = [];
$expiringSoonItems = [];

try {
    // Get current date
    $currentDate = date('Y-m-d');
    
    // Query for already expired items
    $expiredQuery = $pdo->prepare("
        SELECT 
            id,
            product_name,
            quantity_received,
            expiry_date,
            DATEDIFF(expiry_date, :current_date) AS remaining_days
        FROM products
        WHERE expiry_date < :current_date
        ORDER BY expiry_date ASC
    ");
    $expiredQuery->execute([':current_date' => $currentDate]);
    $expiredItems = $expiredQuery->fetchAll();
    
    // Query for items expiring soon (within 30 days)
    $expiringSoonQuery = $pdo->prepare("
        SELECT 
            id,
            product_name,
            quantity_received,
            expiry_date,
            DATEDIFF(expiry_date, :current_date) AS remaining_days
        FROM products
        WHERE expiry_date >= :current_date 
        AND expiry_date <= DATE_ADD(:current_date, INTERVAL 30 DAY)
        ORDER BY expiry_date ASC
    ");
    $expiringSoonQuery->execute([':current_date' => $currentDate]);
    $expiringSoonItems = $expiringSoonQuery->fetchAll();

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
    <title>Expired Items Report</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-container {
            padding: 20px;
            margin-top: 20px;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .negative-days {
            color: #dc3545;
            font-weight: bold;
        }
        .warning-days {
            color: #fd7e14;
            font-weight: bold;
        }
        .positive-days {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container report-container">
        <h1 class="text-center mb-4">Product Expiry Report</h1>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        
        <!-- Expired Items Section -->
        <div class="expired-section">
            <h2 class="section-title text-danger">Expired Products</h2>
            
            <?php if (empty($expiredItems)): ?>
                <div class="alert alert-success">No expired products found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Quantity Available</th>
                                <th>Expiry Date</th>
                                <th>Days Expired</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiredItems as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['quantity_received']) ?></td>
                                <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                                <td class="negative-days">
                                    <?= abs($item['remaining_days']) ?> days ago
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Expiring Soon Section -->
        <div class="expiring-soon-section">
            <h2 class="section-title text-warning">Products Expiring Soon (within 30 days)</h2>
            
            <?php if (empty($expiringSoonItems)): ?>
                <div class="alert alert-success">No products expiring soon.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Quantity Available</th>
                                <th>Expiry Date</th>
                                <th>Days Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expiringSoonItems as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= htmlspecialchars($item['quantity_received']) ?></td>
                                <td><?= htmlspecialchars($item['expiry_date']) ?></td>
                                <td class="<?= $item['remaining_days'] <= 7 ? 'warning-days' : 'positive-days' ?>">
                                    <?= $item['remaining_days'] ?> days
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>