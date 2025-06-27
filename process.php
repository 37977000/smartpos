<?php
// Process scanned barcode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $barcode = filter_var($data['barcode'], FILTER_SANITIZE_STRING);

    // Validate and process the barcode
    if (is_valid_barcode($barcode)) {
        $product_info = get_product_info($barcode);
        echo "Valid barcode: $barcode\nProduct: " . $product_info;
    } else {
        http_response_code(400);
        echo "Invalid barcode format";
    }
}

function is_valid_barcode($code) {
    // Add your validation logic here
    return preg_match('/^[0-9]{12,13}$/', $code); // Example for EAN-13
}

function get_product_info($barcode) {
    // Example database lookup
    $products = [
        '123456789012' => 'Product A',
        '987654321098' => 'Product B'
    ];
    
    return $products[$barcode] ?? 'Unknown product';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Barcode Scanner</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
</head>
<body>
    <div id="camera-preview" style="width: 500px; height: 300px;"></div>
    <div id="result"></div>

    <script>
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#camera-preview'),
                constraints: {
                    facingMode: "environment" // Use rear camera
                }
            },
            decoder: {
                readers: ["code_128_reader", "ean_reader", "upc_reader"] // Supported formats
            }
        }, function(err) {
            if (err) {
                console.error(err);
                return;
            }
            Quagga.start();
        });

        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            document.getElementById('result').innerHTML = `Scanned: ${code}`;
            
            // Send to PHP backend
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ barcode: code })
            })
            .then(response => response.text())
            .then(data => {
                console.log('Server response:', data);
            });
            
            Quagga.stop(); // Stop after successful scan
        });
    </script>
</body>
</html>