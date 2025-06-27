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
$message = '';
$product = [];
$categories = [];

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories for dropdown
    $stmt = $conn->query("SELECT category_name FROM category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get product details if ID is provided
    if (isset($_GET['id'])) {
        $productId = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
        $productId = $_POST['product_id'];
        $productName = trim($_POST['product_name']);
        $newQuantity = (int)$_POST['quantity_received'];
        $buyingPrice = (float)$_POST['buying_price'];
        $sellingPrice = (float)$_POST['selling_price'];
        $categoryName = trim($_POST['category_name']);
        
        // Get current quantity
        $stmt = $conn->prepare("SELECT quantity_received FROM products WHERE id = :id");
        $stmt->bindParam(':id', $productId);
        $stmt->execute();
        $currentQuantity = $stmt->fetchColumn();
        
        // Calculate total quantity
        $totalQuantity = $currentQuantity + $newQuantity;
        
        // Update product
        $stmt = $conn->prepare("UPDATE products SET 
                               product_name = :product_name, 
                               quantity_received = :quantity_received, 
                               buying_price = :buying_price, 
                               selling_price = :selling_price, 
                               category_name = :category_name 
                               WHERE id = :id");
        
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':quantity_received', $totalQuantity, PDO::PARAM_INT);
        $stmt->bindParam(':buying_price', $buyingPrice);
        $stmt->bindParam(':selling_price', $sellingPrice);
        $stmt->bindParam(':category_name', $categoryName);
        $stmt->bindParam(':id', $productId);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Product updated successfully!</div>';
            // Refresh product data
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
            $stmt->bindParam(':id', $productId);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = '<div class="alert alert-danger">Error updating product.</div>';
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
  <title>Edit Product</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .form-container {
      max-width: 600px;
      margin: 0 auto;
    }
    .quantity-info {
      font-size: 0.9rem;
      color: #6c757d;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title">Edit Product</h3>
          </div>
          <div class="card-body">
            <?php echo $message; ?>
            
            <?php if (!empty($product)): ?>
            <form method="POST" action="">
              <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
              
              <div class="mb-3">
                <label for="productName" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="productName" name="product_name" 
                       value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
              </div>
              
              <div class="mb-3">
                <label for="quantityReceived" class="form-label">Additional Quantity Received</label>
                <input type="number" class="form-control" id="quantityReceived" name="quantity_received" 
                       min="0" value="0" required>
                <div class="quantity-info mt-1">
                  Current quantity: <?php echo $product['quantity_received']; ?>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="buyingPrice" class="form-label">Buying Price</label>
                <input type="number" step="0.01" class="form-control" id="buyingPrice" name="buying_price" 
                       value="<?php echo htmlspecialchars($product['buying_price']); ?>" min="0.01" required>
              </div>
              
              <div class="mb-3">
                <label for="sellingPrice" class="form-label">Selling Price</label>
                <input type="number" step="0.01" class="form-control" id="sellingPrice" name="selling_price" 
                       value="<?php echo htmlspecialchars($product['selling_price']); ?>" min="0.01" required>
              </div>
              
              <div class="mb-3">
                <label for="categoryName" class="form-label">Category</label>
                <select class="form-select" id="categoryName" name="category_name" required>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category); ?>" 
                      <?php if ($category === $product['category_name']) echo 'selected'; ?>>
                      <?php echo htmlspecialchars($category); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              
              <button type="submit" class="btn btn-primary" name="update">Update Product</button>
              <a href="product_search.php" class="btn btn-secondary">Cancel</a>
            </form>
            <?php else: ?>
              <div class="alert alert-warning">Product not found.</div>
              <a href="product_search.php" class="btn btn-primary">Back to Products</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (Optional) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>