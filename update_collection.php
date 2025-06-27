<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientId = $_POST['client_id'] ?? null;
    $quantities = $_POST['quantities'] ?? null;

    if (!$clientId || !$quantities) {
        echo "<div class='alert alert-danger'>Invalid request, missing client or quantities.</div>";
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Fetch client info
        $clientStmt = $pdo->prepare("SELECT full_name FROM trust WHERE client_id = :client_id");
        $clientStmt->execute([':client_id' => $clientId]);
        $client = $clientStmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            throw new Exception("Client not found in trust table.");
        }

        $clientName = $client['full_name'];
        $now = date('Y-m-d H:i:s');

        // Process each waiting list item
        foreach ($quantities as $itemId => $quantity) {
            // Get item from waiting list
            $stmt = $pdo->prepare("SELECT * FROM waiting_list WHERE id = :item_id AND client_id = :client_id");
            $stmt->execute([':item_id' => $itemId, ':client_id' => $clientId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                // Update waiting list quantity
                $updateStmt = $pdo->prepare("UPDATE waiting_list SET quantity = :quantity WHERE id = :item_id");
                $updateStmt->execute([':quantity' => $quantity, ':item_id' => $itemId]);

                // Get product ID from products table by product name
                $productStmt = $pdo->prepare("SELECT id FROM products WHERE product_name = :product_name LIMIT 1");
                $productStmt->execute([':product_name' => $item['product']]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    // Update product inventory
                    $updateInventoryStmt = $pdo->prepare("UPDATE products 
                                                          SET quantity_received = quantity_received - :quantity 
                                                          WHERE id = :product_id");
                    $updateInventoryStmt->execute([
                        ':quantity' => $quantity,
                        ':product_id' => $product['id']
                    ]);
                }

                // Insert into picked table
                $pickedStmt = $pdo->prepare("INSERT INTO picked (client_name, product, quantity, created_at)
                                             VALUES (:client_name, :product, :quantity, :created_at)");
                $pickedStmt->execute([
                    ':client_name' => $clientName,
                    ':product' => $item['product'],
                    ':quantity' => $quantity,
                    ':created_at' => $now
                ]);
            }
        }

        // Remove the client from trust table
        $deleteTrust = $pdo->prepare("DELETE FROM trust WHERE client_id = :client_id");
        $deleteTrust->execute([':client_id' => $clientId]);

        // Commit everything
        $pdo->commit();

        // Redirect with success
        $message = urlencode("Collection updated and trust cleared for $clientName.");
        header("Location: global.php?message=$message");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Database Error: " . $e->getMessage() . "</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-warning'>" . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request method.</div>";
}
?>
