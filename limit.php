<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }

// Include database configuration
require_once 'connection.php'; // This will provide the $pdo connection

$successMessage = $errorMessage = '';
$recentEntries = [];

try {
    // Create table if not exists (using the correct column name 'limited' instead of 'limit')
    $pdo->exec("CREATE TABLE IF NOT EXISTS expiry (
        id INT AUTO_INCREMENT PRIMARY KEY,
        limited INT NOT NULL COMMENT 'Expiry limit in days',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expiryLimit'])) {
        $limit = filter_input(INPUT_POST, 'expiryLimit', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1]
        ]);

        if ($limit === false) {
            throw new Exception("Please enter a valid number greater than 0");
        }

        $stmt = $pdo->prepare("INSERT INTO expiry (limited) VALUES (:limit)");
        $stmt->execute([':limit' => $limit]);

        $successMessage = "Expiry limit of $limit days saved successfully! (ID: " . $pdo->lastInsertId() . ")";
    }

    // Get recent entries
    $stmt = $pdo->query("SELECT id, limited, created_at FROM expiry ORDER BY id DESC LIMIT 5");
    $recentEntries = $stmt->fetchAll();

} catch (PDOException $e) {
    $errorMessage = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Limit Settings</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .expiry-container {
            max-width: 500px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 50px auto;
        }
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="expiry-container bg-white">
                    <h2 class="text-center mb-4">SET LOW ALERT</h2>
                    
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="expiryLimit" class="form-label">Set Low alert</label>
                            <input type="number" class="form-control" id="expiryLimit" 
                                   name="expiryLimit" min="1" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                    
                    <?php if (!empty($recentEntries)): ?>
                    <div class="mt-4">
                        <h5>Recent Entries</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Limit (days)</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentEntries as $entry): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($entry['id']) ?></td>
                                        <td><?= htmlspecialchars($entry['limited']) ?></td>
                                        <td><?= htmlspecialchars($entry['created_at']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>