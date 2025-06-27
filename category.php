<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include('header1.php');

// Database connection details
$host = 'localhost'; // Replace with your host
$dbname = 'pos'; // Replace with your database name
$username = 'podata'; // Replace with your database username
$password = '1234567'; // Replace with your database password

// Initialize variables
$message = '';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['category_name'])) {
        $categoryName = trim($_POST['category_name']);
        
        // Validate input
        if (!empty($categoryName)) {
            // Prepare SQL statement to insert category
            $stmt = $pdo->prepare("INSERT INTO category (category_name) VALUES (:category_name)");
            
            // Bind parameters and execute
            $stmt->bindParam(':category_name', $categoryName);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Category added successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error adding category.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">Please enter a category name.</div>';
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
  <title>Add Category</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Add Category</h3>
          </div>
          <div class="card-body">
            <!-- Display message -->
            <?php echo $message; ?>

            <!-- Form -->
            <form action="" method="POST">
              <div class="mb-3">
                <label for="categoryInput" class="form-label">Category</label>
                <input type="text" class="form-control" id="categoryInput" name="category_name" placeholder="Enter category" required>
              </div>
              <button type="submit" class="btn btn-primary" name="save">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (Optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>