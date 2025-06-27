<?php
$conn = new mysqli("localhost", "username", "password", "your_database");

$customer = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST["client_id"];
    $stmt = $conn->prepare("SELECT * FROM trust WHERE client_id = ?");
    $stmt->bind_param("s", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
}
?>

<form method="POST">
    <input type="text" name="client_id" placeholder="Scan QR code or enter client ID" autofocus required>
    <button type="submit">Search</button>
</form>

<?php if ($customer): ?>
    <h3>Customer Info</h3>
    <p>Name: <?= htmlspecialchars($customer['full_name']) ?></p>
    <p>Phone: <?= htmlspecialchars($customer['phone_number']) ?></p>
    <p>Emergency Contact: <?= htmlspecialchars($customer['emergency_person']) ?> (<?= $customer['emergency_no'] ?>)</p>
    <p>Address: <?= htmlspecialchars($customer['address']) ?></p>
    <p>Mode of Payment: <?= $customer['mode_of_payment'] ?></p>
    <p>Status: <?= $customer['status'] ?></p>
<?php endif; ?>
