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

<!-- Stellar SDK -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/stellar-sdk/12.1.0/stellar-sdk.min.js"></script>

<script type="module">
    // Using esm.sh as it often handles module resolution more reliably for browser-direct imports
    import { isConnected, getPublicKey, signTransaction } from 'https://esm.sh/@stellar/freighter-api';

    console.log("Freighter POS System: Loaded");

    // shared state
    let senderPublicKey = "";

    // Constants from PHP
    <?php
        $parsed_url = parse_url($data['stellar_uri']);
        parse_str($parsed_url['query'], $query_params);
        $destination = $query_params['destination'] ?? '';
    ?>
    const destination = '<?php echo $destination; ?>';
    const amount = '<?php echo $data['request']->amount; ?>';
    const memoText = '<?php echo $data['request']->payment_reference; ?>';
    const reference = '<?php echo $data['request']->payment_reference; ?>';

    const server = new StellarSdk.Horizon.Server("<?php echo STELLAR_HORIZON_URL; ?>");
    const networkPassphrase = "<?php echo STELLAR_NETWORK_PASSPHRASE; ?>";

    /**
     * Connect to Freighter
     */
    async function connectWallet() {
        console.log("Connect Wallet: Initiated");
        const connectBtn = document.getElementById('connect-freighter');
        const payBtn = document.getElementById('pay-freighter');

        try {
            // Check if installed
            const connected = await isConnected();
            if (!connected) {
                alert("Freighter extension not detected. Please install it and REFRESH the page.");
                return;
            }

            // Request permission and get public key
            const pk = await getPublicKey();
            
            if (pk) {
                console.log("Connect Wallet: Success", pk);
                senderPublicKey = pk;
                
                connectBtn.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>Connected: ${pk.substring(0, 5)}...${pk.substring(51)}`;
                connectBtn.classList.replace('btn-info', 'btn-outline-info');
                payBtn.style.display = 'block';
            }
        } catch (error) {
            console.error("Connect Wallet: Error", error);
            alert("Connection failed. Make sure your Freighter wallet is UNLOCKED and you have allowed access.");
        }
    }

    /**
     * Execute Payment
     */
    async function executePayment() {
        if (!senderPublicKey) {
            alert("Please connect your wallet first.");
            return;
        }

        const payBtn = document.getElementById('pay-freighter');

        try {
            payBtn.disabled = true;
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading Account...';

            const account = await server.loadAccount(senderPublicKey);

            const transaction = new StellarSdk.TransactionBuilder(account, {
                fee: StellarSdk.BASE_FEE,
                networkPassphrase: networkPassphrase,
            })
            .addOperation(StellarSdk.Operation.payment({
                destination: destination,
                asset: StellarSdk.Asset.native(),
                amount: amount,
            }))
            .addMemo(StellarSdk.Memo.text(memoText))
            .setTimeout(60)
            .build();

            const xdr = transaction.toXDR();
            
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sign in Freighter...';
            const signedXdr = await signTransaction(xdr, { network: "<?php echo strtoupper(STELLAR_NETWORK); ?>" });
            
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
            const result = await server.submitTransaction(StellarSdk.TransactionBuilder.fromXDR(signedXdr, networkPassphrase));
            
            console.log("Payment: Success", result.hash);
            payBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Payment Successful!';
            payBtn.classList.replace('btn-success', 'btn-outline-success');
        } catch (error) {
            console.error("Payment: Failed", error);
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="bi bi-wallet-fill me-2"></i> Pay with Freighter';
            alert("Transaction failed: " + (error.message || "User rejected or session expired."));
        }
    }

    /**
     * Poll Payment Status
     */
    function startPolling() {
        const checkStatus = () => {
            fetch(`<?php echo URLROOT; ?>/payment/checkStatus/${reference}`)
                .then(res => res.json())
                .then(data => {
                    const statusBox = document.getElementById('payment-status');
                    if (data.status === 'paid') {
                        statusBox.innerHTML = `
                            <div class="alert alert-success mt-3 shadow-sm">
                                <i class="bi bi-check-circle-fill me-2"></i> Payment Received!
                            </div>
                            <a href="<?php echo URLROOT; ?>/dashboard" class="btn btn-success w-100 mt-2">Go to Dashboard</a>
                        `;
                    } else if (data.status === 'expired') {
                        statusBox.innerHTML = `
                            <div class="alert alert-danger mt-3 shadow-sm">
                                <i class="bi bi-x-circle-fill me-2"></i> Request Expired
                            </div>
                            <a href="<?php echo URLROOT; ?>/payment/create" class="btn btn-primary w-100 mt-2">Create New Request</a>
                        `;
                    } else {
                        setTimeout(checkStatus, 10000); // Poll every 10 seconds
                    }
                })
                .catch(err => {
                    console.error('Polling: Error', err);
                    setTimeout(checkStatus, 15000);
                });
        };
        checkStatus();
    }

    // Attach to buttons explicitly after module load
    document.getElementById('connect-freighter').onclick = connectWallet;
    document.getElementById('pay-freighter').onclick = executePayment;

    // Start polling immediately
    startPolling();
</script>

<?php require APPROOT . '/views/layout/footer.php'; ?>
