<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-lg border-warning">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> CRITICAL: Backup Your Secret Key</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong>Warning!</strong> We do NOT store your secret key. If you lose this key, you lose access to your funds forever. There is no password reset for Stellar wallets.
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted">Stellar Public Key (G...)</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace" value="<?php echo $data['public_key']; ?>" readonly id="pubKey">
                        <button class="btn btn-outline-secondary" onclick="copyToClipboard('pubKey')"><i class="bi bi-clipboard"></i></button>
                    </div>
                </div>

                <div class="mb-4 p-3 bg-light border rounded">
                    <label class="form-label text-danger fw-bold">Stellar Secret Key (S...)</label>
                    <div class="input-group">
                        <input type="password" class="form-control font-monospace fw-bold" value="<?php echo $data['secret_key']; ?>" readonly id="secKey">
                        <button class="btn btn-outline-danger" onclick="toggleSecret()"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-danger" onclick="copyToClipboard('secKey')"><i class="bi bi-clipboard"></i></button>
                    </div>
                    <div class="form-text text-danger mt-2">
                        <i class="bi bi-shield-lock me-1"></i> Keep this offline. Never share it with anyone.
                    </div>
                </div>

                <div class="card bg-info bg-opacity-10 mb-4">
                    <div class="card-body py-2">
                        <h5>Backup Instructions:</h5>
                        <ul class="mb-0 small">
                            <li>Write it down on paper and store it in a safe place.</li>
                            <li>Use a password manager (like Bitwarden, LastPass, or 1Password).</li>
                            <li>Save it on an encrypted USB drive.</li>
                        </ul>
                    </div>
                </div>

                <hr>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirmSaved">
                    <label class="form-check-input-label fw-bold" for="confirmSaved">
                        I have backed up my Secret Key and understand that loss of this key means loss of funds.
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button id="finishBtn" class="btn btn-success btn-lg" disabled onclick="window.location.href='<?php echo URLROOT; ?>/wallet/index'">
                        Finish and Go to Wallet
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyToClipboard(id) {
        const input = document.getElementById(id);
        const type = input.type;
        input.type = 'text';
        input.select();
        document.execCommand('copy');
        input.type = type;
        alert('Copied to clipboard!');
    }

    function toggleSecret() {
        const input = document.getElementById('secKey');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('confirmSaved').addEventListener('change', function() {
        document.getElementById('finishBtn').disabled = !this.checked;
    });

    // Prevent leaving page without confirmation
    window.onbeforeunload = function() {
        if (!document.getElementById('confirmSaved').checked) {
            return "Are you sure you want to leave? You MUST backup your secret key or you will lose access to your funds.";
        }
    };
</script>

<?php require APPROOT . '/views/layout/footer.php'; ?>
