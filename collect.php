<?php
session_start();
include 'connection.php';
include 'header1.php';

$clientId = $_GET['client_id'] ?? null;

if (!$clientId) {
    echo "<div class='alert alert-danger'>Client ID is missing!</div>";
    exit();
}

try {
    // Fetch client name and days in
    $stmt = $pdo->prepare("SELECT full_name, DATEDIFF(NOW(), created_at) AS days_in FROM trust WHERE client_id = :client_id");
    $stmt->execute([':client_id' => $clientId]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo "<div class='alert alert-warning'>Client not found.</div>";
        exit();
    }

    // Fetch waiting list items
    $itemsStmt = $pdo->prepare("SELECT * FROM waiting_list WHERE client_id = :client_id");
    $itemsStmt->execute([':client_id' => $clientId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['total'];
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Collect Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5 p-4 shadow rounded bg-white" style="max-width: 800px;">
  <h3 class="text-center mb-4">Client Order for Collection</h3>

  <div class="mb-3">
    <h5>Client: <strong><?= htmlspecialchars($client['full_name']) ?></strong></h5>
    <p>Days since order: <strong><?= $client['days_in'] ?> day(s)</strong></p>
  </div>

  <?php if (!empty($items)): ?>
    <form action="update_collection.php" method="post">
      <input type="hidden" name="client_id" value="<?= htmlspecialchars($clientId) ?>">
      
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Product</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['product']) ?></td>
              <td>
                <input type="number" name="quantities[<?= $item['id'] ?>]" class="form-control" value="<?= $item['quantity'] ?>" min="1">
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td class="text-end"><strong>Total (Ksh):</strong></td>
            <td><strong>Ksh<?= number_format($total, 2) ?></strong></td>
          </tr>
        </tfoot>
      </table>

      <div class="text-center mt-4">
  <a href="sales.php?id=<?= urlencode($clientId) ?>&name=<?= urlencode($client['full_name']) ?>&pickup=1" class="btn btn-success">Add Items</a>
</div>
      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary">Update Collection</button>
      </div>

    </form>
  <?php else: ?>
    <div class="alert alert-info">No items found in the waiting list for this client.</div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
