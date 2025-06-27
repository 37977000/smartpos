<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
include('header1.php');

// Database connection details
$host = 'localhost';
$dbname = 'siele';
$username = 'csejay';
$password = '37977000';

// Initialize variables
$message = '';

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch categories for dropdown
    $categories = [];
    $stmt = $pdo->query("SELECT category_name FROM category");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row['category_name'];
    }

    // Check if form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_name'])) {
        $productName = trim($_POST['product_name']);
        $quantityReceived = (int)$_POST['quantity_received'];
        $buyingPrice = (float)$_POST['buying_price'];
        $sellingPrice = (float)$_POST['selling_price'];
        $categoryName = trim($_POST['category_name']);
        $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        
        // Validate input
        if (!empty($productName) && $quantityReceived > 0 && $buyingPrice > 0 && $sellingPrice > 0 && !empty($categoryName)) {
            // Prepare SQL statement to insert product
            $stmt = $pdo->prepare("INSERT INTO products 
                                  (product_name, quantity_received, buying_price, selling_price, category_name, expiry_date) 
                                  VALUES 
                                  (:product_name, :quantity_received, :buying_price, :selling_price, :category_name, :expiry_date)");
            
            // Bind parameters and execute
            $stmt->bindParam(':product_name', $productName);
            $stmt->bindParam(':quantity_received', $quantityReceived, PDO::PARAM_INT);
            $stmt->bindParam(':buying_price', $buyingPrice);
            $stmt->bindParam(':selling_price', $sellingPrice);
            $stmt->bindParam(':category_name', $categoryName);
            $stmt->bindParam(':expiry_date', $expiryDate);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Product added successfully!</div>';
                // Clear form fields if needed
                $_POST = array();
            } else {
                $message = '<div class="alert alert-danger">Error adding product.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">Please fill all required fields with valid values.</div>';
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
  <title>Add Product</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Flatpickr CSS for date picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    .card {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card-header {
      padding: 1.5rem;
    }
    .form-control, .form-select {
      padding: 0.75rem 1rem;
    }
    .btn-primary {
      padding: 0.75rem 2rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">Add New Product</h3>
          </div>
          <div class="card-body">
            <?php echo $message; ?>

            <form action="" method="POST" class="needs-validation" novalidate>
              <div class="mb-3">
                <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="productName" name="product_name" 
                       value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : ''; ?>" 
                       placeholder="Enter product name" required>
                <div class="invalid-feedback">Please enter a product name</div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="quantityReceived" class="form-label">Quantity Received <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="quantityReceived" name="quantity_received" 
                         value="<?php echo isset($_POST['quantity_received']) ? htmlspecialchars($_POST['quantity_received']) : ''; ?>" 
                         placeholder="Enter quantity" min="1" required>
                  <div class="invalid-feedback">Please enter a valid quantity (minimum 1)</div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="categoryName" class="form-label">Category <span class="text-danger">*</span></label>
                  <select class="form-select" id="categoryName" name="category_name" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                      <option value="<?php echo htmlspecialchars($category); ?>" 
                        <?php if (isset($_POST['category_name']) && $_POST['category_name'] === $category) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Please select a category</div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="buyingPrice" class="form-label">Buying Price <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="buyingPrice" name="buying_price" 
                           value="<?php echo isset($_POST['buying_price']) ? htmlspecialchars($_POST['buying_price']) : ''; ?>" 
                           placeholder="0.00" min="0.01" required>
                  </div>
                  <div class="invalid-feedback">Please enter a valid price</div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="sellingPrice" class="form-label">Selling Price <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="sellingPrice" name="selling_price" 
                           value="<?php echo isset($_POST['selling_price']) ? htmlspecialchars($_POST['selling_price']) : ''; ?>" 
                           placeholder="0.00" min="0.01" required>
                  </div>
                  <div class="invalid-feedback">Please enter a valid price</div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="expiryDate" class="form-label">Expiry Date</label>
                <input type="date" class="form-control" id="expiryDate" name="expiry_date" 
                       value="<?php echo isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : ''; ?>"
                       placeholder="Select expiry date">
                <small class="text-muted">Leave blank if product doesn't expire</small>
              </div>
              
              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="reset" class="btn btn-outline-secondary me-md-2">Clear</button>
                <button type="submit" class="btn btn-primary" name="save">Save Product</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Flatpickr JS -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    // Initialize flatpickr for date picker
    document.addEventListener('DOMContentLoaded', function() {
      flatpickr("#expiryDate", {
        dateFormat: "Y-m-d",
        minDate: "today",
        allowInput: true
      });
      
      // Bootstrap form validation
      (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
          .forEach(function (form) {
            form.addEventListener('submit', function (event) {
              if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
              }
              
              form.classList.add('was-validated')
            }, false)
          })
      })()
    });
  </script>
</body>
</html>