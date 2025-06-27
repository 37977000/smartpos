<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php"); // Redirect to login if not logged in
  exit();
}
include('header1.php');

// Database connection details
$host = 'localhost';
$dbname = 'pos';
$username = 'podata';
$password = '1234567';

$message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vendor_name'])) {
        $vendorName = trim($_POST['vendor_name']);
        
        if (!empty($vendorName)) {
            // Check if vendor already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM vendors WHERE vendor_name = :vendor_name");
            $checkStmt->bindParam(':vendor_name', $vendorName);
            $checkStmt->execute();
            
            if ($checkStmt->fetchColumn() > 0) {
                $message = '<div class="alert alert-warning">Vendor already exists!</div>';
            } else {
                // Insert new vendor if not exists
                $insertStmt = $pdo->prepare("INSERT INTO vendors (vendor_name) VALUES (:vendor_name)");
                $insertStmt->bindParam(':vendor_name', $vendorName);
                
                if ($insertStmt->execute()) {
                    $message = '<div class="alert alert-success">Vendor added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error adding vendor.</div>';
                }
            }
        } else {
            $message = '<div class="alert alert-warning">Please enter a vendor name.</div>';
        }
    }
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Vendor</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* ... (keep your existing styles unchanged) ... */
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Add Vendor</h3>
          </div>
          <div class="card-body">
            <?php echo $message; ?>
            <form action="" method="POST">
              <div class="mb-3">
                <label for="vendorInput" class="form-label">VENDOR NAME</label>
                <input type="text" class="form-control" id="vendorInput" name="vendor_name" 
                       placeholder="Enter vendor name" required>
              </div>
              <button type="submit" class="btn btn-primary" name="save">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>