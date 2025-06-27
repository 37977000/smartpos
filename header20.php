<?php
include 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Bar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .dropdown-menu {
            border-radius: 0;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .dropdown-item {
            padding: 8px 15px;
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #212529;
        }
        .dropdown-divider {
            margin: 5px 0;
        }
        .nav-item.dropdown:hover .dropdown-menu {
            display: block;
        }
        .navbar-brand {
            font-weight: 600;
        }
        body {
            padding-top: 60px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand" href="index.php">HOME</a>

            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    
                    <!-- Sales Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="salesDropdown" role="button" data-bs-toggle="dropdown">
                            Sales
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="salesDropdown">
                            <li><a class="dropdown-item" href="sales.php">View Sales</a></li>
                            <li><a class="dropdown-item" href="sales_report.php">Sales Reports</a></li>
                            <li><a class="dropdown-item" href="daily_sales.php">Daily Sales</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="sales_analytics.php">Sales Analytics</a></li>
                        </ul>
                    </li>
                    
                    <!-- Category Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoryDropdown" role="button" data-bs-toggle="dropdown">
                            low price
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                            <li><a class="dropdown-item" href="category.php">Set  stock alert</a></li>
                            <li><a class="dropdown-item" href="rooms.php">expiry limit</a></li>
                            <li><a class="dropdown-item" href="standard.php">standard charges</a></li>
                        </ul>
                    </li>
                    
                    <!-- Stock Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="stockDropdown" role="button" data-bs-toggle="dropdown">
                            Stock
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="stockDropdown">
                            <li><a class="dropdown-item" href="goods.php">Add Stock</a></li>
                            <li><a class="dropdown-item" href="managestock.php">Manage Stock</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="stock_report.php">Stock Report</a></li>
                            <li><a class="dropdown-item" href="low_stock.php">Low Stock Alert</a></li>
                        </ul>
                    </li>
                    
                    <!-- User Management Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            User Management
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="adduser.php">ADD USER</a></li>
                            <li><a class="dropdown-item" href="editusers.php">STAFF REGISTER</a></li>
                            <li><a class="dropdown-item" href="user_roles.php">User Roles</a></li>
                        </ul>
                    </li>
                    
                    <!-- Logout -->
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Log out</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS (Required for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>