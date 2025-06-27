<?php
session_start();

class MpesaStkPush {
    private $consumerKey;
    private $consumerSecret;
    private $shortCode;
    private $passkey;
    private $callbackUrl;

    public function __construct($shortCode, $passkey, $callbackUrl) {
        $this->consumerKey = 'p8zLidBSllI9qyBFKxrQpqnITmzGhjH51t9344SLwwkI3nvu'; // sandbox key
        $this->consumerSecret = 'knEARAoq2ASoB0xQwp5jiQWt7Eel0FuLFWTeUAHOGcoWGrDtGDIIklhjegHrXBfB'; // sandbox secret
        $this->shortCode = $shortCode;
        $this->passkey = $passkey;
        $this->callbackUrl = $callbackUrl;
    }

    private function generateAccessToken() {
        $url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret)
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("cURL Error: $error");
        }

        curl_close($curl);
        $data = json_decode($response);

        if (!isset($data->access_token)) {
            throw new Exception("Access token generation failed. Response: " . $response);
        }

        return $data->access_token;
    }

    private function generateTimestamp() {
        return date('YmdHis');
    }

    private function generatePassword($timestamp) {
        return base64_encode($this->shortCode . $this->passkey . $timestamp);
    }

    public function initiateStkPush($phone, $amount, $accountReference, $transactionDesc) {
        $timestamp = $this->generateTimestamp();
        $password = $this->generatePassword($timestamp);
        $accessToken = $this->generateAccessToken();

        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $data = [
            'BusinessShortCode' => $this->shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $this->callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception("STK Push cURL Error: $error");
        }

        curl_close($curl);

        if ($httpCode !== 200) {
            throw new Exception("STK Push failed. HTTP $httpCode. Response: $response");
        }

        return json_decode($response);
    }
}

// === Main execution starts here ===
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Amount from form
    if (!isset($_POST['mpesa_amount'])) {
        throw new Exception("Payment amount not specified");
    }

    $amount = floatval($_POST['mpesa_amount']);
    if ($amount <= 0) {
        throw new Exception("Invalid payment amount");
    }

    // Get phone number from form (updated to match cart input name)
    $phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : null;

    if (!$phone || !preg_match('/^254[0-9]{9}$/', $phone)) {
        throw new Exception("Invalid phone number format. Use format 2547XXXXXXXX");
    }

    // Init STK push class
    $mpesa = new MpesaStkPush(
        '174379', // Sandbox shortcode
        'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919', // Sandbox passkey
        'https://yourdomain.com/callback.php' // Replace with your actual public callback URL
    );

    $response = $mpesa->initiateStkPush(
        $phone,
        $amount,
        'ORDER_' . uniqid(),
        'Payment for Order'
    );

    // Validate response
    if (!isset($response->ResponseCode) || $response->ResponseCode !== "0") {
        throw new Exception($response->errorMessage ?? $response->ResponseDescription ?? 'Payment request failed');
    }

    // Store transaction info in session
    $_SESSION['mpesa_transaction'] = [
        'amount' => $amount,
        'phone' => $phone,
        'merchant_request_id' => $response->MerchantRequestID,
        'checkout_request_id' => $response->CheckoutRequestID,
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'pending'
    ];

    header("Location: cart.php?message=" . urlencode("M-Pesa payment request of KSh " . number_format($amount, 2) . " sent to $phone"));
    exit();

} catch (Exception $e) {
    error_log("M-Pesa Error: " . $e->getMessage());
    header("Location: cart.php?error=" . urlencode("M-Pesa Error: " . $e->getMessage()));
    exit();
}
