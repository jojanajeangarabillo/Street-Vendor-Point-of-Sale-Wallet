<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Payment Request</h1>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <form id="paymentForm">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (XLM) <sup>*</sup></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">XLM</span>
                            <input type="number" step="0.0000001" class="form-control" id="amount" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" rows="3" placeholder="e.g. Hot Dog, Coffee..."></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-qr-code me-2"></i> Generate QR Code
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow text-center py-5 border-dashed" id="qrContainer">
            <div class="card-body">
                <div class="mb-4">
                    <i class="bi bi-qr-code text-muted" style="font-size: 8rem;"></i>
                </div>
                <h5 class="text-muted">QR Code will appear here</h5>
                <p class="text-muted small">Fill out the form to generate a payment request</p>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layout/footer.php'; ?>
