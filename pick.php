<?php
session_start();
include 'connection.php'; // your connection code
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Picked Items</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
    }
    .container {
      margin-top: 50px;
    }
    #searchInput {
      max-width: 400px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<div class="container shadow p-4 bg-white rounded">
  <h3 class="mb-4 text-center">Picked Items</h3>

  <!-- Search input -->
  <input type="text" id="searchInput" class="form-control mx-auto" placeholder="Search by client name or product...">

  <div class="table-responsive mt-3">
    <table class="table table-bordered table-hover" id="pickedTable">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Client Name</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Picked At</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM picked ORDER BY created_at DESC");
            $count = 1;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $count++ . "</td>";
                echo "<td>" . htmlspecialchars($row['client_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['product']) . "</td>";
                echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                echo "</tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='5' class='text-danger text-center'>Error: " . $e->getMessage() . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Bootstrap JS + Search Filter Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const searchInput = document.getElementById('searchInput');
  const tableRows = document.querySelectorAll('#pickedTable tbody tr');

  searchInput.addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    tableRows.forEach(row => {
      const clientName = row.cells[1].textContent.toLowerCase();
      const product = row.cells[2].textContent.toLowerCase();
      row.style.display = clientName.includes(filter) || product.includes(filter) ? '' : 'none';
    });
  });
</script>

</body>
</html>
