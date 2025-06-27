<?php
/**
 * M-Pesa STK Push Callback Handler
 * Save to: D:/xampppp/htdocs/safaricom/callback.php
 * Ngrok URL: https://your-ngrok-url.ngrok.io/safaricom/callback.php
 */

// 1. Log raw incoming data (for debugging)
$logFile = 'mpesa_callback.log';
$rawData = file_get_contents('php://input');
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] RAW DATA:\n" . $rawData . "\n\n", FILE_APPEND);

// 2. Decode JSON data
$callbackData = json_decode($rawData);

// 3. Validate callback structure
if (!isset($callbackData->Body->stkCallback)) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] INVALID CALLBACK STRUCTURE\n", FILE_APPEND);
    header('Content-Type: application/json');
    die(json_encode(["ResultCode" => 1, "ResultDesc" => "Invalid callback data"]));
}

// 4. Extract transaction details
$stkCallback = $callbackData->Body->stkCallback;
$resultCode = $stkCallback->ResultCode;
$resultDesc = $stkCallback->ResultDesc;

// 5. Handle successful payment
if ($resultCode == 0) {
    $metadata = $stkCallback->CallbackMetadata->Item;
    $transactionData = [
        'Amount' => $metadata[0]->Value,
        'MpesaReceiptNumber' => $metadata[1]->Value,
        'PhoneNumber' => $metadata[3]->Value,
        'TransactionDate' => $metadata[2]->Value
    ];

    // 6. Save to database (example)
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=mpesa', 'root', '');
        $stmt = $pdo->prepare("INSERT INTO transactions 
            (receipt_number, phone, amount, transaction_date, result_code) 
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $transactionData['MpesaReceiptNumber'],
            $transactionData['PhoneNumber'],
            $transactionData['Amount'],
            $transactionData['TransactionDate'],
            $resultCode
        ]);
    } catch (PDOException $e) {
        file_put_contents