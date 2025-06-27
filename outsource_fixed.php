<?php
session_start();
$message = '';
include 'connection.php';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch data for form
    $products = $pdo->query("SELECT DISTINCT product_name, category_name FROM products")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT DISTINCT category_name FROM products")->fetchAll(PDO::FETCH_COLUMN, 0);
    $vendors = $pdo->query("SELECT vendor_name FROM vendors")->fetchAll(PDO::FETCH_COLUMN, 0);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $product_name = htmlspecialchars(trim($_POST['product_name']));
        $quantity = filter_var(trim($_POST['quantity']), FILTER_SANITIZE_NUMBER_INT);
        $category = htmlspecialchars(trim($_POST['category']));
        $vendor_name = htmlspecialchars(trim($_POST['vendor_name']));

        if (!empty($product_name) && !empty($quantity) && !empty($category) && !empty($vendor_name)) {
            if ($quantity > 0) {
                // Validate product-category combination
                $checkProduct = $pdo->prepare("SELECT id FROM products 
                                               WHERE product_name = ? 
                                               AND category_name = ?");
                $checkProduct->execute([$product_name, $category]);
                
                if ($checkProduct->rowCount() === 0) {
                    $message = '<div class="alert alert-danger">Invalid product-category combination</div>';
                } else {
                    $pdo->beginTransaction();
                    
                    try {
                        // 1. Handle Outsource Table (Update or Insert)
                        $stmt = $pdo->prepare("
                            INSERT INTO outsource 
                                (product_name, quantity, category, vendor_name, created_at) 
                            VALUES 
                                (:product, :qty, :cat, :vendor, NOW())
                            ON DUPLICATE KEY UPDATE 
                                quantity = quantity + VALUES(quantity),
                                created_at = NOW()
                        ");
                        $stmt->execute([
                            ':product' => $product_name,
                            ':qty' => $quantity,
                            ':cat' => $category,
                            ':vendor' => $vendor_name
                        ]);

                        // 2. Update Products Table
                        $updateStmt = $pdo->prepare("
                            UPDATE products 
                            SET quantity = quantity + :qty 
                            WHERE product_name = :product 
                            AND vendor_name = :vendor
                        ");
                        $updateStmt->execute([
                            ':qty' => $quantity,
                            ':product' => $product_name,
                            ':vendor' => $vendor_name
                        ]);

                        $pdo->commit();
                        $message = '<div class="alert alert-success mt-3">Operation successful!</div>';
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $message = '<div class="alert alert-danger mt-3">Error: ' . $e->getMessage() . '</div>';
                    }
                }
            } else {
                $message = '<div class="alert alert-warning mt-3">Quantity must be positive</div>';
            }
        } else {
            $message = '<div class="alert alert-warning mt-3">Fill all fields</div>';
        }
    }
} catch (PDOException $e) {
    $message = '<div class="alert alert-danger mt-3">Database error: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Outsource Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .centered-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f2f5;
        }
        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            max-width: 600px;
            width: 100%;
        }
        .form-control-custom {
            height: 50px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .select2-container--bootstrap5 .select2-selection { 
            height: 50px;
            padding: 12px 15px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        .btn-custom {
            height: 50px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="centered-container">
            <div class="form-container">
                <h2 class="text-center mb-4" style="color: #2d3748;">Add Outsource Product</h2>
                <?php echo $message; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label for="product_name" class="form-label">Product Name</label>
                        <select class="form-select select2" id="product_name" name="product_name" required>
                            <option value="">Search product...</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= htmlspecialchars($product['product_name']) ?>"
                                    data-category="<?= htmlspecialchars($product['category_name']) ?>">
                                    <?= htmlspecialchars($product['product_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control form-control-custom" 
                               id="category" name="category" 
                               readonly style="background-color: #f8f9fa;">
                    </div>

                    <div class="mb-4">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control form-control-custom" 
                               id="quantity" name="quantity" 
                               min="1" required>
                    </div>

                    <div class="mb-4">
                        <label for="vendor_name" class="form-label">Vendor Name</label>
                        <select class="form-select select2" id="vendor_name" name="vendor_name" required>
                            <option value="">Search vendor...</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= htmlspecialchars($vendor) ?>">
                                    <?= htmlspecialchars($vendor) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-custom">Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#product_name').select2({
                theme: 'bootstrap-5',
                placeholder: 'Type to search...',
                minimumInputLength: 1
            });

            $('#vendor_name').select2({
                theme: 'bootstrap-5',
                placeholder: 'Type to search...',
                minimumInputLength: 1
            });

            // Auto-fill category when product is selected
            $('#product_name').on('change', function() {
                const selectedProduct = $(this).find(':selected');
                $('#category').val(selectedProduct.data('category'));
            });
