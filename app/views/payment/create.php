<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create Payment Request</h1>
</div>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow">
            <div class="card-body">
                <form action="<?php echo URLROOT; ?>/payment/create" method="post">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (XLM) <sup>*</sup></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">XLM</span>
                            <input type="number" step="0.0000001" name="amount" class="form-control <?php echo (!empty($data['amount_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['amount']; ?>" placeholder="0.00" required>
                            <span class="invalid-feedback"><?php echo $data['amount_err']; ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="e.g. Hot Dog, Coffee..."><?php echo $data['description']; ?></textarea>
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
</div>

<?php require APPROOT . '/views/layout/footer.php'; ?>
