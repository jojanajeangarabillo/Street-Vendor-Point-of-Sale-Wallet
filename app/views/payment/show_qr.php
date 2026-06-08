<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Scan to Pay</h1>
    <a href="<?php echo URLROOT; ?>/payment/create" class="btn btn-outline-secondary btn-sm">New Request</a>
</div>

<div class="row">
    <div class="col-md-6 mx-auto text-center">
        <div class="card shadow">
            <div class="card-body">
                <div class="mb-4">
                    <img src="<?php echo $data['qr_code']; ?>" alt="Payment QR Code" class="img-fluid" style="max-width: 300px;">
                </div>
                <h3 class="text-primary"><?php echo $data['request']->amount; ?> XLM</h3>
                <p class="text-muted"><?php echo $data['request']->description; ?></p>
                <div class="alert alert-info py-2">
                    <small>Ref: <strong><?php echo $data['request']->payment_reference; ?></strong></small>
                </div>
                
                <div id="payment-status" class="mt-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Waiting for payment...</span>
                </div>

                <div class="mt-4">
                    <a href="<?php echo $data['stellar_uri']; ?>" class="btn btn-outline-primary w-100">
                        <i class="bi bi-wallet2 me-2"></i> Open in Wallet App
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const reference = '<?php echo $data['request']->payment_reference; ?>';
    const checkStatus = () => {
        fetch(`<?php echo URLROOT; ?>/payment/checkStatus/${reference}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'paid') {
                    document.getElementById('payment-status').innerHTML = `
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i> Payment Received!
                        </div>
                        <a href="<?php echo URLROOT; ?>/dashboard" class="btn btn-success w-100">Go to Dashboard</a>
                    `;
                } else {
                    setTimeout(checkStatus, 5000); // Poll every 5 seconds
                }
            })
            .catch(err => console.error('Error checking status:', err));
    };

    // Start polling
    checkStatus();
</script>

<?php require APPROOT . '/views/layout/footer.php'; ?>
