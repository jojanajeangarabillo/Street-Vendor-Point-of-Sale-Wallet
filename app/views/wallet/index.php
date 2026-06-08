<?php require APPROOT . '/views/layout/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Wallet Management</h1>
</div>

<div class="row">
    <!-- Wallet Info -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Stellar Wallet Details</h5>
            </div>
            <div class="card-body">
                <?php flash('wallet_msg'); ?>
                <div class="mb-3">
                    <label class="text-muted small">Public Key</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" value="<?php echo $data['wallet']->stellar_public_key ?? 'Not connected'; ?>" readonly id="publicKey">
                        <?php if($data['wallet']): ?>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('publicKey')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Network</label>
                    <div><span class="badge bg-info text-dark"><?php echo ucfirst($data['wallet']->network ?? 'testnet'); ?></span></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Balance</label>
                    <h3 class="text-primary"><?php echo $data['balance']; ?> XLM</h3>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <?php if(!$data['wallet']): ?>
                    <form action="<?php echo URLROOT; ?>/wallet/createWallet" method="post" class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i> Create New Wallet
                        </button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-outline-success" onclick="location.reload()">
                        <i class="bi bi-arrow-repeat me-2"></i> Refresh Balance
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-outline-dark" disabled>
                        <i class="bi bi-box-arrow-in-right me-2"></i> Connect Freighter Wallet (Coming Soon)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Getting Started</h5>
            </div>
            <div class="card-body">
                <ol class="list-group list-group-numbered list-group-flush">
                    <li class="list-group-item">Generate a new wallet or connect your Freighter extension.</li>
                    <li class="list-group-item">Ensure you are on the <strong>Stellar Testnet</strong>.</li>
                    <li class="list-group-item">Fund your wallet using the <a href="https://laboratory.stellar.org/#friendbot" target="_blank">Friendbot</a>.</li>
                    <li class="list-group-item">Once funded, you can start creating payment requests.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(id) {
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    alert("Copied: " + copyText.value);
}
</script>

<?php require APPROOT . '/views/layout/footer.php'; ?>
