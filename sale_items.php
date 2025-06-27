<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
include('header1.php');

// Database connection
$host = "localhost";
$username = "csejay";
$password = "37977000";
$dbname = "bamwai";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get date filter from GET parameters
    $date_filter = $_GET['date_filter'] ?? date('Y-m-d');
    
    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT 
            product_name,
            SUM(quantity) as total_quantity,
            SUM(total_price) as total_amount
        FROM 
            sale_items
        WHERE 
            DATE(date) = :date_filter
        GROUP BY 
            product_name
        ORDER BY 
            total_quantity DESC
    ");
    $stmt->bindParam(':date_filter', $date_filter);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            margin-top: 20px;
        }
        .filter-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .print-btn {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sales Report</h2>
            <button onclick="window.print()" class="btn btn-success print-btn">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
        
        <!-- Filter Form -->
        <div class="filter-container">
            <form method="get" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label for="date_filter" class="form-label">Filter by Date:</label>
                        <input type="date" class="form-control" id="date_filter" name="date_filter" 
                               value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="?date_filter=<?php echo date('Y-m-d'); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-calendar-day"></i> Today
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($results) > 0): ?>
                        <?php 
                        $counter = 1;
                        $grand_total_qty = 0;
                        $grand_total_amount = 0;
                        ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo number_format($row['total_quantity']); ?></td>
                                <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                            </tr>
                            <?php 
                            $grand_total_qty += $row['total_quantity'];
                            $grand_total_amount += $row['total_amount'];
                            ?>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2"><strong>Grand Total</strong></td>
                            <td><strong><?php echo number_format($grand_total_qty); ?></strong></td>
                            <td><strong>₱<?php echo number_format($grand_total_amount, 2); ?></strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No sales found for the selected date</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-info">
            <strong>Report Date:</strong> <?php echo date('F j, Y', strtotime($date_filter)); ?>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default date picker to today
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('date_filter');
            if (!dateFilter.value) {
                dateFilter.valueAsDate = new Date();
            }
        });
    </script>
</body>
</html>