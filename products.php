<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include ('header1.php');
// Database connection details
$host = 'localhost'; // Replace with your host
$dbname = 'pos'; // Replace with your database name
$username = 'podata'; // Replace with your database username
$password = '1234567'; // Replace with your database password

// Initialize variables
$categories = [];
$products = [];
$selectedCategory = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories from the `category` table
    $stmt = $conn->query("SELECT category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if the form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $selectedCategory = $_POST['category'];

        // Fetch products for the selected category
        $stmt = $conn->prepare("SELECT product_name, quantity_received, selling_price FROM products WHERE category_name = :category_name");
        $stmt->bindParam(':category_name', $selectedCategory);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Handle database errors
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Search</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Optional: Add custom CSS for better alignment */
    .form-container {
      display: flex;
      gap: 10px; /* Space between elements */
      align-items: center; /* Vertically center elements */
    }
    .form-container .form-control {
      flex: 1; /* Allow input boxes to grow and fill space */
    }
    .table-container {
      margin-top: 20px; /* Add space between the form and the table */
    }
  </style>
</head>
<body>
  <div class="container mt-3">
    <!-- Form Container -->
    <div class="form-container">
      <!-- Category Dropdown -->
      <select class="form-select" id="category" name="category" required>
        <option value="">Select a category</option>
        <?php
        // Populate the dropdown with categories
        foreach ($categories as $category) {
            $selected = ($category === $selectedCategory) ? 'selected' : '';
            echo "<option value='$category' $selected>$category</option>";
        }
        ?>
      </select>

      <!-- Search Button -->
      <button type="submit" class="btn btn-success" name="search">Search</button>

      <!-- View All Button -->
      <button type="submit" class="btn btn-info" name="view_all">View All</button>

      <!-- View Cart Button -->
      <button type="submit" class="btn btn-warning" name="view_cart">View Cart</button>
    </div>

    <!-- Table Container -->
    <div class="table-container">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Product Name</th>
            <th>Quantity Received</th>
            <th>Selling Price</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Populate the table with products
          if (!empty($products)) {
              foreach ($products as $product) {
                  echo "<tr>
                          <td>{$product['product_name']}</td>
                          <td>{$product['quantity_received']}</td>
                          <td>{$product['selling_price']}</td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='3' class='text-center'>No products found for the selected category.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Bootstrap JS (Optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>