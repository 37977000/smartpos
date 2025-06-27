<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'connection.php';

// ... [existing vendor fetch code] ...

// Handle return actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $vendor_name = $_POST['vendor_name'];
    $category = $_POST['category'];
    $return_qty = $_POST['return_qty'];
    
    try {
        $pdo->beginTransaction();

        // 1. Update outsource table
        $stmt = $pdo->prepare("
            UPDATE outsource 
            SET quantity = quantity - ? 
            WHERE product_name = ? 
            AND vendor_name = ? 
            AND category = ?
        ");
        $stmt->execute([$return_qty, $product_name, $vendor_name, $category]);
        
        // 2. Update products table
        $stmt = $pdo->prepare("
            UPDATE products 
            SET quantity_received = quantity_received - ? 
            WHERE product_name = ? 
            AND category_name = ?
        ");
        $stmt->execute([$return_qty, $product_name, $category]);
        
        // 3. Insert into returned table
        $stmt = $pdo->prepare("
            INSERT INTO returned 
            (vendor_name, product_name, category, quantity, user, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $vendor_name,
            $product_name,
            $category,
            $return_qty,
            $_SESSION['username'],
            date('Y-m-d H:i:s') // Current timestamp
        ]);

        // Check products update
        if ($stmt->rowCount() === 0) {
            throw new PDOException("Product not found in inventory");
        }

        $pdo->commit();
        $_SESSION['message'] = '<div class="alert alert-success">Return processed and recorded!</div>';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message'] = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<!-- The HTML part remains the same as in original code -->
<!-- The HTML part remains exactly the same as in your original code -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hardware Returns Management</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .search-box {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        table {
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-return {
            transition: all 0.3s ease;
        }
        .btn-return:hover {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
        }
        .autocomplete-items {
            position: absolute;
            border: 1px solid #ddd;
            border-top: none;
            z-index: 99;
            background: white;
            width: calc(100% - 30px);
            max-height: 200px;
            overflow-y: auto;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
            background: white; 
        }
        .autocomplete-item:hover {
            background-color: #f1f1f1;
        }
        .autocomplete-wrapper {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container-wrapper">
        <div class="container">
            <?php if(isset($_SESSION['message'])): ?>
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <div class="search-box">
                <form method="GET" class="row g-3">
                    <div class="col-md-8 autocomplete-wrapper">
                        <label class="form-label">Hardware Borrowed From</label>
                        <input type="text" class="form-control" name="vendor" 
                               id="vendorInput" autocomplete="off"
                               value="<?= htmlspecialchars($_GET['vendor'] ?? '') ?>" 
                               placeholder="Enter vendor name">
                        <div id="autocompleteList" class="autocomplete-items"></div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>

            <?php if(isset($_GET['vendor'])): 
                try {
                    $stmt = $pdo->prepare("
                        SELECT * FROM outsource 
                        WHERE vendor_name = ?
                    ");
                    $stmt->execute([$_GET['vendor']]);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">'.$e->getMessage().'</div>';
                }
                ?>
                
                <?php if(count($results) > 0): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Category</th>
                                <th>Quantity to Return</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= htmlspecialchars($row['category']) ?></td>
                                    <td style="width: 150px">
                                        <form method="POST">
                                            <input type="hidden" name="product_name" 
                                                   value="<?= htmlspecialchars($row['product_name']) ?>">
                                            <input type="hidden" name="vendor_name" 
                                                   value="<?= htmlspecialchars($row['vendor_name']) ?>">
                                            <input type="hidden" name="category" 
                                                   value="<?= htmlspecialchars($row['category']) ?>">
                                            <input type="number" class="form-control" 
                                                   name="return_qty" min="1" 
                                                   max="<?= $row['quantity'] ?>" required>
                                    </td>
                                    <td>
                                            <button type="submit" class="btn btn-outline-primary btn-return">
                                                Return
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-return"
                                                    onclick="this.form.return_qty.value=<?= $row['quantity'] ?>">
                                                 RETURN All
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">No records found for this vendor</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const vendors = <?= json_encode($vendors) ?>;
        const vendorInput = document.getElementById('vendorInput');
        const autocompleteList = document.getElementById('autocompleteList');

        function showSuggestions(val) {
            autocompleteList.innerHTML = '';
            const input = val.trim().toLowerCase();
            if (!input) return;
            
            const matches = vendors.filter(vendor => 
                vendor.toLowerCase().includes(input)
            ).slice(0, 5);

            matches.forEach(vendor => {
                const div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.textContent = vendor;
                div.onclick = () => {
                    vendorInput.value = vendor;
                    autocompleteList.innerHTML = '';
                };
                autocompleteList.appendChild(div);
            });
        }

        vendorInput.addEventListener('input', (e) => showSuggestions(e.target.value));
        
        document.addEventListener('click', (e) => {
            if (e.target !== vendorInput) {
                autocompleteList.innerHTML = '';
            }
        });
    </script>
</body>
</html>