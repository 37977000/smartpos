<?php
session_start();
require 'connection.php'; // Ensure this loads $pdo

// Debugging output
error_log("Entering save_later.php");
error_log("Session: " . print_r($_SESSION, true));
error_log("POST: " . print_r($_POST, true));

if (!isset($_SESSION['pending_later_sale'])) {
  error_log("Error: pending_later_sale not set");
  header("Location: cart.php?error=Sale ID missing");
  exit();
}

if (!isset($_POST['customer_name'])) {
  error_log("Error: customer_name not set");
  header("Location: cart.php?error=Customer name required");
  exit();
}

try {
  $sale_id = $_SESSION['pending_later_sale'];
  $customer_name = $_POST['customer_name'];

  error_log("Processing sale ID: $sale_id");

  // Rest of your PDO code here...
  
} catch (PDOException $e) {
  error_log("Database Error: " . $e->getMessage());
  header("Location: cart.php?error=Database error");
  exit();
}