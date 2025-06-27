<?php
session_start();
if (!isset($_SESSION['pending_later_sale'])) {
    header("Location: cart.php");
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Information</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<body>
  <form method="POST" action="save_later.php">
    <input type="text" name="customer_name" required>
    <button type="submit">Submit</button>
  </form>
</body>
</body>
</html>