<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
include('header1.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operations Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .operation-container {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .btn-custom {
            width: 200px;
            height: 60px;
            margin: 15px;
            background-color: #6c757d;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #ffd700;
            color: #212529;
            transform: scale(1.05);
        }
        .button-group {
            background-color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body>
    <div class="operation-container d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center button-group">
                    <h2 class="mb-4">Operations Menu</h2>
                    <div class="d-flex flex-column align-items-center">
                        <a href="outsource.php" class="btn btn-custom">BORROW>></a>
                        <a href="return.php" class="btn btn-custom"><< Return</a>
                        <a href="debt.php" class="btn btn-custom">Debt</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>