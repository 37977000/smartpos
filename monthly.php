<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
include ('header1.php');
$servername = "localhost"; // Change if necessary
$username = "csejay"; // Change if necessary
$password = "37977000"; // Change if necessary
$dbname = "bamwai"; // Change if necessary

try {
    // Change $host to $servername in the connection string
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Rest of your code remains the same...

// Initialize variables
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$startDate = date('Y-m-01', strtotime($selectedMonth));
$endDate = date('Y-m-t', strtotime($selectedMonth));

// Get all cashiers
$cashiersQuery = $pdo->prepare("SELECT DISTINCT username FROM sales ORDER BY username");
$cashiersQuery->execute();
$cashiers = $cashiersQuery->fetchAll(PDO::FETCH_COLUMN);

// Get sales data for the selected month
$salesData = [];
$grandTotal = 0;

foreach ($cashiers as $cashier) {
    $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM sales 
                          WHERE username = :username 
                          AND sale_date BETWEEN :startDate AND :endDate");
    $stmt->execute([':username' => $cashier, ':startDate' => $startDate, ':endDate' => $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total']) {
        $salesData[$cashier] = $result['total'];
        $grandTotal += $result['total'];
    }
}

// Get detailed transactions for each cashier
$transactions = [];
if (!empty($salesData)) {
    $stmt = $pdo->prepare("SELECT id, sale_date, total_amount, payment_method, amount_tendered, username 
                          FROM sales 
                          WHERE sale_date BETWEEN :startDate AND :endDate
                          ORDER BY username, sale_date ASC");
    $stmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Sales Report</title>
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-header {
            background-color: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .cashier-header {
            background-color: #dee2e6;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="report-header">
            <h1 class="text-center">Monthly Sales Report</h1>
            
            <form method="get" class="row g-3 mt-3">
                <div class="col-md-6">
                    <label for="month" class="form-label">Select Month:</label>
                    <input type="month" class="form-control" id="month" name="month" 
                           value="<?php echo htmlspecialchars($selectedMonth); ?>" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>

        <?php if (!empty($salesData)): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Cashier Totals</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Cashier</th>
                                        <th class="text-end">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($salesData as $cashier => $total): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cashier); ?></td>
                                            <td class="text-end"><?php echo number_format($total, 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td>Grand Total</td>
                                        <td class="text-end"><?php echo number_format($grandTotal, 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Detailed Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Cashier</th>
                                <th>Payment Method</th>
                                <th class="text-end">Amount Tendered</th>
                                <th class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $currentCashier = null;
                            foreach ($transactions as $transaction): 
                                if ($currentCashier !== $transaction['username']):
                                    $currentCashier = $transaction['username'];
                            ?>
                                <tr class="cashier-header">
                                    <td colspan="6"><?php echo htmlspecialchars($currentCashier); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($transaction['sale_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
                                    <td class="text-end"><?php echo number_format($transaction['amount_tendered'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($transaction['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No sales data found for the selected month.</div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>