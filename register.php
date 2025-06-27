<?php
require __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

// Database connection
$conn = new mysqli("localhost", "csejay", "37977000", "bamwai");

// Function to generate unique numeric client ID
function generateNumericClientId($conn) {
    do {
        // Generate 6-digit numeric ID with leading zeros
        $client_id = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $check = $conn->prepare("SELECT client_id FROM trust WHERE client_id = ?");
        $check->bind_param("s", $client_id);
        $check->execute();
        $result = $check->get_result();
    } while ($result->num_rows > 0);

    return $client_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate numeric client ID
    $client_id = generateNumericClientId($conn);
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $emergency_no = $_POST['emergency_no'];
    $emergency_person = $_POST['emergency_person'];
    $address = $_POST['address'];
    $mode_of_payment = $_POST['mode_of_payment'];
    $status = "active";

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO trust (client_id, full_name, phone_number, emergency_no, emergency_person, address, mode_of_payment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $client_id, $full_name, $phone_number, $emergency_no, $emergency_person, $address, $mode_of_payment, $status);
    $stmt->execute();

    // Prepare customer data
    $customer_data = [
        'client_id' => $client_id,
        'full_name' => $full_name,
        'phone_number' => $phone_number,
        'emergency_no' => $emergency_no,
        'emergency_person' => $emergency_person,
        'address' => $address,
        'mode_of_payment' => $mode_of_payment
    ];

    $customer_data_json = json_encode($customer_data);

    // Create cards directory if it doesn't exist
    $cardsDir = __DIR__ . '/cards';
    if (!is_dir($cardsDir)) {
        mkdir($cardsDir, 0755, true);
    }

    // Generate QR code
    $result = Builder::create()
        ->writer(new PngWriter())
        ->data($customer_data_json) // Change to $client_id if you only want the ID in QR
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->build();

    // Save QR code
    $result->saveToFile("{$cardsDir}/{$client_id}.png");

    // Display results
    echo "<div class='text-center mt-4'>";
    echo "<h3 class='text-success'>Customer Registered!</h3>";
    echo "<img src='cards/{$client_id}.png' alt='Customer QR Code' class='img-fluid' style='max-width: 200px;' />";
    echo "<p class='mt-2'><strong>Client ID:</strong> $client_id</p>";

    echo "<h4 class='mt-4'>Customer Information</h4>";
    echo "<p><strong>Full Name:</strong> $full_name</p>";
    echo "<p><strong>Phone Number:</strong> $phone_number</p>";
    echo "<p><strong>Emergency Number:</strong> $emergency_no</p>";
    echo "<p><strong>Emergency Contact Person:</strong> $emergency_person</p>";
    echo "<p><strong>Address:</strong> $address</p>";
    echo "<p><strong>Mode of Payment:</strong> $mode_of_payment</p>";

    echo "<button onclick='window.print()' class='btn btn-primary mt-3'>Print Card</button>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .center-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .custom-btn:hover {
            background-color: #198754 !important;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container center-container">
        <div class="card shadow p-4" style="width: 100%; max-width: 500px;">
            <h4 class="mb-4 text-center">Register & Generate Card</h4>
            <form method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="phone_number" placeholder="Phone Number" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="emergency_no" placeholder="Emergency Number" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="emergency_person" placeholder="Emergency Contact Person" required>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" name="address" placeholder="Address" required></textarea>
                </div>
                <div class="mb-3">
                    <select class="form-select" name="mode_of_payment">
                        <option value="Cash">Cash</option>
                        <option value="Insurance">Insurance</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-success w-100 custom-btn">Register & Generate Card</button>
            </form>
        </div>
    </div>
</body>
</html>
