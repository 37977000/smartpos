<?php
include ('header1.php');
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expiry Limit Settings</title>
    <!-- Bootstrap 5 CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .expiry-container {
            max-width: 500px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="expiry-container bg-white">
            <h2 class="text-center mb-4">Expiry Limit Settings</h2>
            
            <form id="expiryForm">
                <div class="mb-3">
                    <label for="expiryLimit" class="form-label">Set Expiry Limit (days)</label>
                    <input type="number" class="form-control" id="expiryLimit" min="1" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery for AJAX (you can use fetch API instead if preferred) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#expiryForm').submit(function(e) {
            e.preventDefault();
            
            const limitValue = $('#expiryLimit').val();
            
            // AJAX call to insert into database
            $.ajax({
                url: 'save_expiry.php', // Your server-side script
                type: 'POST',
                data: { limit: limitValue },
                success: function(response) {
                    alert('Expiry limit saved successfully!');
                    // You could also update a table display here if needed
                },
                error: function() {
                    alert('Error saving expiry limit.');
                }
            });
        });
    });
    </script>
</body>
</html>