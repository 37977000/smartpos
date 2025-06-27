<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
// Include your PDO database connection file
include('connection.php'); // Make sure this file creates a PDO connection

// Initialize variables
$salesData = [];
$totalSales = 0;
$selectedDate = date('Y-m-d'); // Default to today's date

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date'])) {
    $selectedDate = $_POST['date'];
    
    try {
        // Modified query to include username
        $query = "SELECT id, sale_date, total_amount, payment_method, amount_tendered, username
                  FROM sales 
                  WHERE DATE(sale_date) = :sale_date 
                  ORDER BY sale_date DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':sale_date', $selectedDate);
        $stmt->execute();
        
        $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($salesData as $sale) {
            $totalSales += $sale['total_amount'];
        }
        
    } catch (PDOException $e) {
        die("Error fetching sales data: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report by Date</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-container {
            margin-top: 30px;
        }
        .total-display {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container report-container">
        <h2 class="text-center mb-4">Sales Report by Date</h2>
        
        <!-- Date Filter Form -->
        <form method="POST" class="mb-4">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="date" name="date" class="form-control" 
                               value="<?= htmlspecialchars($selectedDate) ?>" 
                               max="<?= date('Y-m-d') ?>"
                               required>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </div>
        </form>
        
        <!-- Display total sales -->
        <?php if (!empty($salesData)): ?>
            <div class="total-display text-center">
                Total Sales for <?= htmlspecialchars($selectedDate) ?>: 
                <span class="text-success">Kshs<?= number_format($totalSales, 2) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Sales Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sale Date</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Amount Tendered</th>
                        <th>Cashier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salesData)): ?>
                        <?php foreach ($salesData as $sale): ?>
                            <tr>
                                <td><?= htmlspecialchars($sale['id']) ?></td>
                                <td><?= htmlspecialchars($sale['sale_date']) ?></td>
                                <td>Kshs<?= number_format($sale['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                                <td>Kshs<?= number_format($sale['amount_tendered'], 2) ?></td>
                                <td><?= htmlspecialchars($sale['username'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No sales found for the selected date</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>