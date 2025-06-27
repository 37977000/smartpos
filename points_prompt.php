<!DOCTYPE html>
<html>
<head>
    <title>Load Loyalty Points</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Loyalty Points Program</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="checkout.php" enctype="multipart/form-data">
                    <div class="mb-4">
                        <p class="lead">Your total exceeds Ksh 2,000! Earn loyalty points:</p>
                        <div class="alert alert-info">
                            <strong>1 point = Ksh 100 spent</strong><br>
                            Points can be redeemed for discounts on future purchases
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="load_points" 
                                       id="yes" value="yes" required>
                                <label class="form-check-label" for="yes">
                                    <strong>Yes</strong>, I want to earn points
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="load_points" 
                                       id="no" value="no">
                                <label class="form-check-label" for="no">
                                    <strong>No</strong>, continue without points
                                </label>
                            </div>
                        </div>

                        <div class="mt-3" id="qrSection" style="display: none;">
                            <div class="card card-body bg-light">
                                <h5 class="card-title">Scan Your QR Code</h5>
                                <input type="file" class="form-control-file" name="qr_code" 
                                       accept="image/*" required>
                                <small class="form-text text-muted">
                                    Upload your loyalty program QR code (JPG/PNG, max 2MB)
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="cart.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Cart
                        </a>
                        <button type="submit" class="btn btn-success">
                            Continue to Payment <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle QR code upload visibility
        document.querySelectorAll('input[name="load_points"]').forEach(input => {
            input.addEventListener('change', () => {
                const qrSection = document.getElementById('qrSection');
                qrSection.style.display = input.value === 'yes' ? 'block' : 'none';
                qrSection.querySelector('input').required = input.value === 'yes';
            });
        });
    </script>
</body>
</html>