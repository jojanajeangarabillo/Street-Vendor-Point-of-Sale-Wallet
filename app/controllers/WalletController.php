<?php

require_once APPROOT . '/services/StellarService.php';

class WalletController extends Controller {
    private $walletModel;
    private $stellarService;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->walletModel = $this->model('Wallet');
        $this->stellarService = new StellarService();
    }

    /**
     * Display Wallet Management page
     */
    public function index() {
        $wallet = $this->walletModel->getWalletByVendor($_SESSION['vendor_id']);
        $balance = '0.0000000';

        if ($wallet) {
            $balance = $this->stellarService->getBalance($wallet->stellar_public_key);
        }

        $data = [
            'title' => 'Wallet Management',
            'wallet' => $wallet,
            'balance' => $balance
        ];

        $this->view('wallet/index', $data);
    }

    /**
     * Generate and save a new Stellar Wallet
     */
    public function createWallet() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if user already has a wallet
            if ($this->walletModel->getWalletByVendor($_SESSION['vendor_id'])) {
                flash('wallet_msg', 'A wallet already exists for this account.', 'alert alert-danger');
                redirect('wallet/index');
                return;
            }

            try {
                // Generate Keypair
                $keypair = $this->stellarService->generateKeypair();

                $walletData = [
                    'vendor_id' => $_SESSION['vendor_id'],
                    'public_key' => $keypair['publicKey'],
                    'wallet_type' => 'internal',
                    'network' => STELLAR_NETWORK
                ];

                if ($this->walletModel->saveWallet($walletData)) {
                    // Attempt to fund with Friendbot (only on testnet)
                    if (STELLAR_NETWORK === 'testnet') {
                        $this->stellarService->fundAccount($keypair['publicKey']);
                    }
                    
                    // Instead of flash message, we'll show a dedicated backup page
                    $data = [
                        'title' => 'Backup Your Wallet',
                        'public_key' => $keypair['publicKey'],
                        'secret_key' => $keypair['secretKey']
                    ];
                    $this->view('wallet/backup', $data);
                    return;
                } else {
                    flash('wallet_msg', 'Database error: Could not save wallet.', 'alert alert-danger');
                }
            } catch (Exception $e) {
                flash('wallet_msg', 'Error: ' . $e->getMessage(), 'alert alert-danger');
            }

            redirect('wallet/index');
        }
    }

    /**
     * AJAX endpoint to fetch balance
     */
    public function getBalance() {
        $wallet = $this->walletModel->getWalletByVendor($_SESSION['vendor_id']);
        if ($wallet) {
            $balance = $this->stellarService->getBalance($wallet->stellar_public_key);
            echo json_encode(['balance' => $balance]);
        } else {
            echo json_encode(['error' => 'No wallet found']);
        }
    }
}
