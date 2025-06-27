<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $clientRaw = $_GET['client'] ?? null;
    $order = $_SESSION['order_backup'] ?? null;

    if (!$clientRaw || !$order || empty($order['items'])) {
        die("Invalid client or order.");
    }

    list($clientId, $clientName) = explode('|', $clientRaw, 2);
    $createdAt = $order['created_at'] ?? date('Y-m-d H:i:s');
    $username = $_SESSION['username'] ?? 'system';

    try {
        $pdo->beginTransaction();

        // Calculate total
        $totalAmount = array_reduce($order['items'], function ($sum, $item) {
            return $sum + $item['total_price'];
        }, 0);

        // Insert into sales
        $salesStmt = $pdo->prepare("INSERT INTO sales (sale_date, total_amount, payment_method, amount_tendered, username)
                                    VALUES (:sale_date, :total_amount, 'later', 0, :username)");
        $salesStmt->execute([
            ':sale_date' => $createdAt,
            ':total_amount' => $totalAmount,
            ':username' => $username
        ]);
        $saleId = $pdo->lastInsertId();

        foreach ($order['items'] as $item) {
            $product = $item['product_name'];
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['selling_price'];
            $total = $price * $quantity;

            // Update or insert into waiting_list
            $checkStmt = $pdo->prepare("SELECT id, quantity FROM waiting_list 
                                        WHERE client_id = :client_id AND product = :product LIMIT 1");
            $checkStmt->execute([':client_id' => $clientId, ':product' => $product]);

            if ($existing = $checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $newQty = $existing['quantity'] + $quantity;
                $update = $pdo->prepare("UPDATE waiting_list 
                                         SET quantity = :quantity, total = :total 
                                         WHERE id = :id");
                $update->execute([
                    ':quantity' => $newQty,
                    ':total' => $newQty * $price,
                    ':id' => $existing['id']
                ]);
            } else {
                $insert = $pdo->prepare("INSERT INTO waiting_list (client_id, full_name, product, quantity, total, created_at)
                                         VALUES (:client_id, :full_name, :product, :quantity, :total, :created_at)");
                $insert->execute([
                    ':client_id' => $clientId,
                    ':full_name' => $clientName,
                    ':product' => $product,
                    ':quantity' => $quantity,
                    ':total' => $total,
                    ':created_at' => $createdAt
                ]);
            }

            // Insert into sale_items
            $itemStmt = $pdo->prepare("INSERT INTO sale_items 
                (sale_id, product_id, product_name, quantity, price, total_price, date)
                VALUES (:sale_id, :product_id, :product_name, :quantity, :price, :total_price, :date)");
            $itemStmt->execute([
                ':sale_id' => $saleId,
                ':product_id' => $productId,
                ':product_name' => $product,
                ':quantity' => $quantity,
                ':price' => $price,
                ':total_price' => $total,
                ':date' => $createdAt
            ]);
        }

        $pdo->commit();
        unset($_SESSION['order_backup']);

        // Redirect to sales with success
        $message = urlencode("Items successfully added to $clientName's tab.");
        header("Location: sales.php?status=pickup_saved&message=$message");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: waiting.php");
    exit();
}
