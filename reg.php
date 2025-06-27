<?php
session_start();
include('header1.php');
include('connection.php');

$error = '';
$success = '';
$formData = []; // To store submitted form data

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $formData = [
        'fullName' => trim($_POST['fullName'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'emergencyNumber' => trim($_POST['emergencyNumber'] ?? ''),
        'emergencyPerson' => trim($_POST['emergencyPerson'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'paymentMode' => trim($_POST['paymentMode'] ?? '')
    ];

    // Validate required fields
    if (empty($formData['fullName'])) {
        $error = 'Full Name is required';
    } elseif (empty($formData['phone'])) {
        $error = 'Phone Number is required';
    } elseif (empty($formData['emergencyNumber'])) {
        $error = 'Emergency Number is required';
    } elseif (empty($formData['emergencyPerson'])) {
        $error = 'Emergency Contact Person is required';
    } elseif (empty($formData['address'])) {
        $error = 'Address is required';
    } elseif (empty($formData['paymentMode'])) {
        $error = 'Payment Mode is required';
    }

    if (empty($error)) {
        try {
            $sql = "INSERT INTO trust (
                        full_name, 
                        phone_number, 
                        emergency_no, 
                        emergency_person, 
                        address, 
                        mode_of_payment, 
                        status,
                        created_at
                    ) VALUES (
                        :fullName, 
                        :phone, 
                        :emergencyNo, 
                        :emergencyPerson, 
                        :address, 
                        :paymentMode, 
                        'active',
                        :createdAt
                    )";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fullName' => $formData['fullName'],
                ':phone' => $formData['phone'],
                ':emergencyNo' => $formData['emergencyNumber'],
                ':emergencyPerson' => $formData['emergencyPerson'],
                ':address' => $formData['address'],
                ':paymentMode' => $formData['paymentMode'],
                ':createdAt' => date('Y-m-d H:i:s') // current datetime
            ]);
    
            $success = 'Information submitted successfully!';
            $formData = [];
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centered Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .centered-container {
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid centered-container d-flex justify-content-center align-items-center">
        <div class="container shadow-lg p-4 rounded" style="max-width: 600px;">
            <h2 class="text-center mb-4">Contact Information</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <!-- Full Name -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" 
                               value="<?= htmlspecialchars($formData['fullName'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Phone Number and Emergency Number -->
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="emergencyNumber" class="form-label">Emergency Number</label>
                        <input type="tel" class="form-control" id="emergencyNumber" name="emergencyNumber" 
                               value="<?= htmlspecialchars($formData['emergencyNumber'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="emergencyPerson" class="form-label">Emergency Contact Person</label>
                        <input type="text" class="form-control" id="emergencyPerson" name="emergencyPerson" 
                               value="<?= htmlspecialchars($formData['emergencyPerson'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Address -->
                <div class="row mb-3">
                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" 
                                  rows="3" required><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Payment Mode -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="paymentMode" class="form-label">Mode of Payment</label>
                        <select class="form-select" id="paymentMode" name="paymentMode" required>
                            <option value="">Choose...</option>
                            <option value="Credit Card" <?= ($formData['paymentMode'] ?? '') === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                            <option value="Debit Card" <?= ($formData['paymentMode'] ?? '') === 'Debit Card' ? 'selected' : '' ?>>Debit Card</option>
                            <option value="Net Banking" <?= ($formData['paymentMode'] ?? '') === 'Net Banking' ? 'selected' : '' ?>>Net Banking</option>
                            <option value="Cash" <?= ($formData['paymentMode'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>