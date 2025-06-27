<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include('header1.php');
// Database connection details
$host = 'localhost';
$dbname = 'pos';
$username = 'podata';
$password = '1234567';

// Initialize variables
$categories = [];
$products = [];
$selectedCategory = '';
$productName = '';

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories from the `category` table
    $stmt = $conn->query("SELECT category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['search'])) {
            // Search by product name and/or category
            $productName = trim($_POST['product_name']);
            $selectedCategory = $_POST['category'];
            
            // Build the SQL query
            $sql = "SELECT id, product_name, quantity_received, selling_price, category_name FROM products WHERE 1=1";
            $params = [];
            
            if (!empty($productName)) {
                $sql .= " AND product_name LIKE :product_name";
                $params[':product_name'] = '%' . $productName . '%';
            }
            
            if (!empty($selectedCategory)) {
                $sql .= " AND category_name = :category";
                $params[':category'] = $selectedCategory;
            }
            
            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } elseif (isset($_POST['view_all'])) {
            // View all products
            $stmt = $conn->query("SELECT id, product_name, quantity_received, selling_price, category_name FROM products");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $productName = '';
            $selectedCategory = '';
        }
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
    .form-container {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .form-container .form-control {
      flex: 1;
    }
    .table-container {
      margin-top: 20px;
    }
    .category-dropdown {
      width: 200px;
    }
    .action-buttons {
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div class="container mt-3">
    <!-- Form Container -->
    <form method="POST" action="">
      <div class="form-container">
        <!-- Product Name Input -->
        <input type="text" class="form-control" id="product_name" name="product_name" 
               placeholder="Enter product name" value="<?php echo htmlspecialchars($productName); ?>">

        <!-- Category Dropdown -->
        <select class="form-select category-dropdown" id="category" name="category">
          <option value="">Select a category</option>
          <?php
          foreach ($categories as $category) {
              $selected = ($category === $selectedCategory) ? 'selected' : '';
              echo "<option value='" . htmlspecialchars($category) . "' $selected>" . htmlspecialchars($category) . "</option>";
          }
          ?>
        </select>

        <!-- Search Button -->
        <button type="submit" class="btn btn-success" name="search">Search</button>

        <!-- View All Button -->
        <button type="submit" class="btn btn-info" name="view_all">View All</button>
      </div>
    </form>

    <!-- Table Container -->
    <div class="table-container">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>Item Name</th>
            <th>Quantity Available</th>
            <th>Category</th>
            <th>Selling Price</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (!empty($products)) {
              foreach ($products as $product) {
                  echo "<tr>
                          <td>" . htmlspecialchars($product['product_name']) . "</td>
                          <td>" . htmlspecialchars($product['quantity_received']) . "</td>
                          <td>" . htmlspecialchars($product['category_name']) . "</td>
                          <td>" . number_format($product['selling_price'], 2) . "</td>
                          <td class='action-buttons'>
                            <a href='edit_stock.php?id=" . $product['id'] . "' class='btn btn-warning btn-sm'>Edit</a>
                            <button class='btn btn-danger btn-sm'>Delete</button>
                          </td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='5' class='text-center'>No products found. Use the search or click 'View All' to see products.</td></tr>";
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