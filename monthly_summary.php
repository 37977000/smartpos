<?php
session_start();
include('header1.php');

// Database connection
$host = 'localhost';
$dbname = 'pos';
$username = 'podata';
$password = '1234567';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get month from POST or GET
    $reportMonth = $_POST['month'] ?? ($_GET['month'] ?? date('Y-m'));
    
    // Validate month format
    if (!DateTime::createFromFormat('Y-m', $reportMonth)) {
        throw new Exception("Invalid month format");
    }

    // Query to get monthly totals
    $stmt = $conn->prepare("
        SELECT 
            SUM(amount_tendered) as monthly_total,
            COUNT(*) as transaction_count,
            COUNT(DISTINCT username) as cashier_count
        FROM sales
        WHERE DATE_FORMAT(sale_date, '%Y-%m') = :reportMonth
    ");
    $stmt->bindParam(':reportMonth', $reportMonth);
    $stmt->execute();
    $monthlyData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to get daily breakdown
    $dailyStmt = $conn->prepare("
        SELECT 
            DATE(sale_date) as day,
            SUM(amount_tendered) as daily_total,
            COUNT(*) as daily_transactions
        FROM sales
        WHERE DATE_FORMAT(sale_date, '%Y-%m') = :reportMonth
        GROUP BY DATE(sale_date)
        ORDER BY DATE(sale_date)
    ");
    $dailyStmt->bindParam(':reportMonth', $reportMonth);
    $dailyStmt->execute();
    $dailyBreakdown = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Monthly Sales Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-header { background-color: #f8f9fa; padding: 20px; border-radius: 5px; }
        .summary-card { background-color: #e9ecef; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .daily-row:nth-child(odd) { background-color: #f8f9fa; }
        @media print { 
            .no-print { display: none !important; } 
            body { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <div class="report-header text-center">
                <h2>Monthly Sales Summary - <?= htmlspecialchars(date('F Y', strtotime($reportMonth))) ?></h2>
            </div>
            
            <div class="summary-card">
                <div class="row">
                    <div class="col-md-4">
                        <h5>Total Collected:</h5>
                        <h3>₱<?= number_format($monthlyData['monthly_total'], 2) ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5>Total Transactions:</h5>
                        <h3><?= $monthlyData['transaction_count'] ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5>Cashiers:</h5>
                        <h3><?= $monthlyData['cashier_count'] ?></h3>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5>Daily Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-borderless mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Transactions</th>
                                <th>Amount Collected</th>
                                <th class="no-print">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dailyBreakdown as $day): ?>
                                <tr class="daily-row">
                                    <td><?= date('M j, Y', strtotime($day['day'])) ?></td>
                                    <td><?= $day['daily_transactions'] ?></td>
                                    <td>₱<?= number_format($day['daily_total'], 2) ?></td>
                                    <td class="no-print">
                                        <a href="daily_report.php?date=<?= $day['day'] ?>" 
                                           class="btn btn-sm btn-info">
                                           View Day
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
            <a href="report.php" class="btn btn-secondary">Back to Reports</a>
            <button onclick="window.print()" class="btn btn-primary ms-2">Print Report</button>
        </div>
    </div>
</body>
</html>