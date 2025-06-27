<?php
session_start();

if (!isset($_SESSION['receipt_data'])) {
    die('No receipt data available. Please complete a purchase first.');
}

$receipt = $_SESSION['receipt_data'];
// Don't unset here - we might need it if user refreshes the print page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?= $receipt['sale_id'] ?></title>
    <style>
        @media print {
            body { font-size: 12pt; }
            .no-print { display: none !important; }
        }
        body {
            font-family: Arial, sans-serif;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .shop-name {
            font-weight: bold;
            font-size: 1.2em;
        }
        .receipt-info {
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
        }
        td {
            padding: 3px 0;
        }
        .total-row {
            border-top: 1px dashed #000;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="shop-name">SLYTECH LTD</div>
        <div>P,O BOX NAKURU</div>
    
    </div>
    
    <div class="receipt-info">
        <div>Receipt #: <?= $receipt['sale_id'] ?></div>
        <div>Date: <?= $receipt['timestamp'] ?></div>
        <div>Cashier: <?= $_SESSION['username'] ?? 'System' ?></div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receipt['cart'] as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₱<?= number_format($item['selling_price'], 2) ?></td>
                <td>₱<?= number_format($item['selling_price'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <table>
        <tr class="total-row">
            <td colspan="3">Subtotal:</td>
            <td>₱<?= number_format($receipt['total'], 2) ?></td>
        </tr>
        <tr>
            <td colspan="3">Payment Method:</td>
            <td><?= strtoupper($receipt['payment_method']) ?></td>
        </tr>
        <tr>
            <td colspan="3">Amount Tendered:</td>
            <td>₱<?= number_format($receipt['amount_tendered'], 2) ?></td>
        </tr>
        <tr class="total-row">
            <td colspan="3">Change:</td>
            <td>₱<?= number_format($receipt['change'], 2) ?></td>
        </tr>
    </table>
    
    <div class="footer">
        Thank you for your purchase!<br>
        Please come again
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Receipt</button>
        <button onclick="window.close()">Close Window</button>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Optional: Close window after print
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>