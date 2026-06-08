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
                    <a href="<?php echo $data['stellar_uri']; ?>" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-wallet2 me-2"></i> Open in Wallet App
                    </a>
                    <button id="connect-freighter" class="btn btn-info w-100 mb-2 text-white">
                        <i class="bi bi-link-45deg me-2"></i> Connect Freighter Wallet
                    </button>
                    <button id="pay-freighter" class="btn btn-success w-100" style="display: none;">
                        <i class="bi bi-wallet-fill me-2"></i> Pay with Freighter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stellar SDK & Freighter API -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/stellar-sdk/12.1.0/stellar-sdk.min.js"></script>
<script src="https://unpkg.com/@stellar/freighter-api@1.1.2/dist/index.min.js"></script>

<script>
    // Constants from PHP
    <?php
        $parsed_url = parse_url($data['stellar_uri']);
        parse_str($parsed_url['query'], $query_params);
        $destination = $query_params['destination'] ?? '';
    ?>
    const destination = '<?php echo $destination; ?>';
    const amount = '<?php echo $data['request']->amount; ?>';
    const memo = '<?php echo $data['request']->payment_reference; ?>';
    const reference = '<?php echo $data['request']->payment_reference; ?>';

    // Stellar Server Configuration
    const server = new StellarSdk.Horizon.Server("https://horizon-testnet.stellar.org");
    const freighter = window.freighterApi;

    // Connect Wallet Logic
    document.getElementById('connect-freighter').addEventListener('click', async () => {
        if (!await freighter.isConnected()) {
            alert("Freighter Wallet not found. Please install the extension.");
            return;
        }

        try {
            const publicKey = await freighter.getPublicKey();
            if (publicKey) {
                document.getElementById('connect-freighter').innerText = `Connected: ${publicKey.substring(0, 5)}...${publicKey.substring(51)}`;
                document.getElementById('connect-freighter').classList.replace('btn-info', 'btn-outline-info');
                document.getElementById('pay-freighter').style.display = 'block';
            }
        } catch (e) {
            console.error(e);
        }
    });

    // Pay with Freighter Logic
    document.getElementById('pay-freighter').addEventListener('click', async () => {
        try {
            const sender = await freighter.getPublicKey();
            const account = await server.loadAccount(sender);

            const transaction = new StellarSdk.TransactionBuilder(account, {
                fee: StellarSdk.BASE_FEE,
                networkPassphrase: StellarSdk.Networks.TESTNET,
            })
            .addOperation(StellarSdk.Operation.payment({
                destination: destination,
                asset: StellarSdk.Asset.native(),
                amount: amount,
            }))
            .addMemo(StellarSdk.Memo.text(memo))
            .setTimeout(60)
            .build();

            const signedXdr = await freighter.signTransaction(transaction.toXDR(), { network: "TESTNET" });
            const result = await server.submitTransaction(StellarSdk.TransactionBuilder.fromXDR(signedXdr, StellarSdk.Networks.TESTNET));
            
            console.log("Success:", result);
            document.getElementById('pay-freighter').innerHTML = '<i class="bi bi-check-circle me-2"></i> Payment Sent!';
            document.getElementById('pay-freighter').disabled = true;
        } catch (error) {
            console.error("Payment Error:", error);
            alert("Payment failed: " + (error.message || "Unknown error"));
        }
    });

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
