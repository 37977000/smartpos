<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
// Include database connection and header
include 'connection.php';
include 'header1.php';

// Generate CAPTCHA code
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = substr(md5(rand()), 0, 6);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CAPTCHA first
    if (empty($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha']) {
        $error = "CAPTCHA verification failed!";
    } else {
        // Get form data
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $role = $_POST['role'];

        // Validate inputs
        if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password) || empty($role)) {
            $error = "All fields are required!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
            $error = "Invalid phone number!";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            try {
                // Prepare and execute the SQL query to insert the new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $phone, $hashed_password, $role]);

                // Display success message and reset CAPTCHA
                $success = "User registered successfully!";
                unset($_SESSION['captcha']);
                
                // Optionally redirect to another page
                // header("Location: success.php");
                // exit();
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
    
    // Generate new CAPTCHA after submission attempt
    $_SESSION['captcha'] = substr(md5(rand()), 0, 6);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-card {
            max-width: 600px;
            margin: 0 auto;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }
        .card-header {
            background-color: #4e73df;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #3a5bbf;
            border-color: #3a5bbf;
        }
        .captcha-container {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .captcha-text {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            letter-spacing: 3px;
            font-weight: bold;
            color: #333;
            user-select: none;
        }
        .input-icon {
            position: relative;
        }
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .input-icon input {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="registration-card card">
            <div class="card-header text-center">
                <h2><i class="fas fa-user-plus me-2"></i>User Registration</h2>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <!-- Username Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <!-- Phone Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" class="form-control" placeholder="Phone Number" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                        <small class="form-text text-muted">Format: 10-15 digits only</small>
                    </div>

                    <!-- Role Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-user-tag"></i>
                        <select name="role" class="form-control" required>
                            <option value="">Select Role</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3 input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>

                    <!-- CAPTCHA Verification -->
                    <div class="mb-4">
                        <label class="form-label">Human Verification</label>
                        <div class="captcha-container mb-2">
                            <span class="captcha-text"><?php echo $_SESSION['captcha']; ?></span>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCaptcha()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                        <input type="text" name="captcha" class="form-control" placeholder="Enter the code above" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i> Register User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        function refreshCaptcha() {
            fetch('refresh_captcha.php')
                .then(response => response.text())
                .then(captcha => {
                    document.querySelector('.captcha-text').textContent = captcha;
                });
        }
        
        // Simple client-side password validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]');
            const confirm = document.querySelector('input[name="confirm_password"]');
            
            if (password.value.length < 8) {
                alert('Password must be at least 8 characters long');
                e.preventDefault();
            } else if (password.value !== confirm.value) {
                alert('Passwords do not match!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>