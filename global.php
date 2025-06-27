<?php
session_start();
include 'connection.php';
include('header1.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Waiting List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
    }
    .center-container {
      min-height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    tr.clickable-row {
      cursor: pointer;
      transition: background-color 0.2s;
    }
    tr.clickable-row:hover {
      background-color: #f5f5f5;
    }
    .btn-collect {
      background-color: #28a745;
      color: white;
      border: none;
    }
    .btn-collect:hover {
      background-color: #218838;
    }
    .btn-add-items {
      background-color: #007bff;
      color: white;
      border: none;
    }
    .btn-add-items:hover {
      background-color: #0056b3;
    }
    .action-buttons {
      display: flex;
      gap: 5px;
    }
  </style>
</head>
<body>

<div class="center-container">
  <div class="container p-4 shadow rounded bg-light" style="max-width: 700px;">
    <h3 class="text-center mb-4">Waiting List</h3>
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Full Name</th>
          <th>Days In</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
          $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          $stmt = $pdo->query("SELECT client_id, full_name, DATEDIFF(NOW(), created_at) AS days_in FROM trust");

          if ($stmt->rowCount() > 0) {
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $clientId = htmlspecialchars($row['client_id'], ENT_QUOTES);
                  $fullName = htmlspecialchars($row['full_name'], ENT_QUOTES);
                  echo "<tr>";
                  echo "<td>{$clientId}</td>";
                  echo "<td>{$fullName}</td>";
                  echo "<td>" . htmlspecialchars($row['days_in']) . "</td>";
                  echo "<td class='action-buttons'>
                          <button class='btn btn-collect btn-sm' data-id='{$clientId}'>Collect</button>
                          <button class='btn btn-add-items btn-sm' data-id='{$clientId}' data-name='{$fullName}'>Add Items</button>
                        </td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='4' class='text-center'>No records found in the waiting list</td></tr>";
          }

        } catch (PDOException $e) {
            echo "<tr><td colspan='4' class='text-center'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Handle Collect button
  document.querySelectorAll('.btn-collect').forEach(button => {
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      const clientId = this.dataset.id;
      window.location.href = `collect.php?client_id=${encodeURIComponent(clientId)}`;
    });
  });

  // Handle Add Items button
  document.querySelectorAll('.btn-add-items').forEach(button => {
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      const clientId = this.dataset.id;
      const clientName = this.dataset.name;
      window.location.href = `sales.php?id=${encodeURIComponent(clientId)}&name=${encodeURIComponent(clientName)}&pickup=1`;
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
