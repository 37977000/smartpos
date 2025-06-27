<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include('header1.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu</title>
    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            /* Removed padding-top: 70px - this was pushing content down */
            /* Background image properties */
            background-image: url('boo.webp');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        
        /* Semi-transparent overlay */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: -1;
        }
        
        .menu-container {
            width: 100%;
            max-width: 300px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin: 30px auto; /* Added margin-top instead of body padding */
            background-color: white;
        }
        
        .menu-btn {
            width: 100%;
            margin: 8px 0;
            padding: 12px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .menu-btn:hover {
            background-color: #ffc107 !important;
            transform: translateY(-2px);
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        a:hover {
            color: inherit;
        }
        
        /* Ensure navbar stays at top */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="menu-container bg-white">
                    <div class="d-grid gap-2">
                        <a href="reports.php" class="btn btn-light menu-btn border">Daily Report</a>
                        <a href="pick.php" class="btn btn-light menu-btn border">collection Report</a>
                        <a href="sale_items.php" class="btn btn-light menu-btn border">Sales</a>
                        <a href="monthly.php" class="btn btn-light menu-btn border">Monthly Report</a>
                        <a href="expired.php" class="btn btn-light menu-btn border">Expired</a>
                        <a href="running_low.php" class="btn btn-light menu-btn border">Running Low</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>