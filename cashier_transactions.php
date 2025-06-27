<?php
session_start();
include('header1.php');

// Database connection
$host = 'localhost';
$dbname = 'siele';
$username = 'csejay';
$password = '37977000';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get date - from POST or GET
    $reportDate = $_POST['date'] ?? ($_GET['date'] ?? date('Y-m-d'));
    
    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $reportDate)) {
        throw new Exception("Invalid date format");
    }

    // Query to get summary by cashier
    $stmt = $conn->prepare("
        SELECT 
            username as cashier_name,
            SUM(amount_tendered) as total_amount,
            COUNT(*) as transaction_count
        FROM sales
        WHERE DATE(sale_date) = :reportDate
        GROUP BY username
        ORDER BY total_amount DESC
    ");
    $stmt->bindParam(':reportDate', $reportDate);
    $stmt->execute();
    $cashiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate grand totals
    $grandTotal = 0;
    $totalTransactions = 0;
    foreach ($cashiers as $cashier) {
        $grandTotal += $cashier['total_amount'];
        $totalTransactions += $cashier['transaction_count'];
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; }
        .total-card { background-color: #e9ecef; padding: 15px; border-radius: 5px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="report-header text-center">
                <h2>Daily Sales Summary - <?= htmlspecialchars(date('F j, Y', strtotime($reportDate))) ?></h2>
            </div>
            
            <div class="total-card mb-4">
                <div class="row">
                    <div class="col-md-6"><h5>Total Collected: ₱<?= number_format($grandTotal, 2) ?></h5></div>
                    <div class="col-md-6"><h5>Total Transactions: <?= $totalTransactions ?></h5></div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h5>Cashier Performance</h5></div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cashier</th>
                                <th>Transactions</th>
                                <th>Total Collected</th>
                                <th class="no-print">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cashiers as $cashier): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cashier['cashier_name']) ?></td>
                                    <td><?= $cashier['transaction_count'] ?></td>
                                    <td>₱<?= number_format($cashier['total_amount'], 2) ?></td>
                                    <td class="no-print">
                                        <a href="cashier_transactions.php?date=<?= $reportDate ?>&cashier=<?= urlencode($cashier['cashier_name']) ?>" 
                                           class="btn btn-sm btn-info">
                                           View Transactions
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="no-print mt-4 text-center">
            <a href="reports.php" class="btn btn-secondary">Back to Reports</a>
            <button onclick="window.print()" class="btn btn-primary ms-2">Print Report</button>
        </div>
    </div>
</body>
</html>