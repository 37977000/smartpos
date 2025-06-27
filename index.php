<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'header2.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Navigation Bar Styles */
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
        
        /* Body and Layout Styles */
        body {
            padding-top: 60px;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow-x: hidden;
            font-family: Arial, sans-serif;
        }
        
        /* Button Container Styles */
        .container-overlay {
            position: relative;
            width: 100%;
            height: calc(100vh - 60px); /* Account for navbar height */
            margin: 0;
            padding: 0;
        }
        
        .background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        
        .button-container {
            position: absolute;
            top: 20px; /* Position at the top of the image */
            left: 0; /* No margin on the left */
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
            margin-top:30px;
        }
        
        .btn-row {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 25px;
        }
        
        /* Custom Button Styles */
        .custom-btn {
            background-color: #28a745;
            border-radius: 30px;
            color: white;
            border: none;
            padding: 12px 25px;
            width: 250px;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s;
            white-space: nowrap;
            display: inline-block;
            text-decoration: none;
            font-size: 16px;
            margin-left: 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .custom-btn:hover {
            background-color: #ffc107; /* Yellow on hover */
            color: #212529;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
        
        /* Logout Button Specific Styles */
        .logout-btn {
            background-color: #dc3545; /* Red color for logout */
        }
        
        .logout-btn:hover {
            background-color: #ff0000; /* Brighter red on hover */
            color: white;
        }
    </style>
</head>
<body>
<!-- Your main content starts here -->
<div class="container-overlay">
    <!-- Background image -->
    <img src="boo.webp" alt="Background image" class="background-image">
    
    <!-- Button container -->
    <div class="button-container">
        <div class="btn-row">
           
            <a href="sales.php" class="custom-btn">SALES</a>
            
            <a href="report.php" class="custom-btn">REPORT</a>
           
            <!-- New Logout Button -->
            <a href="logout.php" class="custom-btn logout-btn">LOGOUT</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>