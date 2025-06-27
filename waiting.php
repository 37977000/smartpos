<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'connection.php';
include 'header1.php';

// Retrieve waiting order from session
$order = $_SESSION['waiting_order'] ?? null;
$_SESSION['order_backup'] = $order; // backup in case needed
unset($_SESSION['waiting_order']); // Clear after retrieval

// Fetch client IDs and names from `trust` table
$clientNames = [];
try {
    $stmt = $pdo->query("SELECT client_id, full_name FROM trust ORDER BY full_name ASC");
    $clientNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientNames = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Waiting Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .order-container {
      max-width: 900px;
      margin: 20px auto;
      padding: 20px;
    }
  </style>
</head>
<body>

<div class="order-container">
  <h2 class="text-center mb-4">Pending Pickup Order</h2>

  <?php if ($order): ?>
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Order Details</h4>
      </div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order['items'] as $item): 
              $subtotal = $item['selling_price'] * $item['quantity'];
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td>Ksh<?= number_format($item['selling_price'], 2) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Ksh<?= number_format($subtotal, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" class="text-end"><strong>Total:</strong></td>
              <td><strong>Ksh<?= number_format($order['total'], 2) ?></strong></td>
            </tr>
            <tr>
              <td colspan="4" class="text-muted">
                Order created at: <?= date('M j, Y g:i a', strtotime($order['created_at'])) ?>
              </td>
            </tr>
          </tfoot>
        </table>

        <!-- Client Name Dropdown -->
        <form method="post" action="save_waiting_order.php">
          <div class="mb-4">
            <label for="clientName" class="form-label"><strong>CLIENT Name</strong></label>
            <select class="form-select" id="clientName" name="client" required>
              <option value="" disabled selected>Select a client</option>
              <?php foreach ($clientNames as $client): ?>
                <option value="<?= htmlspecialchars($client['client_id'] . '|' . $client['full_name']) ?>">
                  <?= htmlspecialchars($client['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-primary">Save for Later</button>
          </div>
        </form>

      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">
      No pending pickup orders found.
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
