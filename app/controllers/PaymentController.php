<?php

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

require_once APPROOT . '/services/StellarService.php';

class PaymentController extends Controller {
    private $paymentModel;
    private $walletModel;
    private $stellarService;

    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
        $this->paymentModel = $this->model('PaymentRequest');
        $this->walletModel = $this->model('Wallet');
        $this->stellarService = new StellarService();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $wallet = $this->walletModel->getWalletByVendor($_SESSION['vendor_id']);
            if (!$wallet) {
                flash('payment_msg', 'Please create a wallet first.', 'alert alert-danger');
                redirect('wallet/index');
                return;
            }

            $data = [
                'vendor_id' => $_SESSION['vendor_id'],
                'amount' => trim($_POST['amount']),
                'description' => trim($_POST['description']),
                'payment_reference' => strtoupper(bin2hex(random_bytes(4))), // Short ref for memo
                'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
                'amount_err' => ''
            ];

            if (empty($data['amount']) || $data['amount'] <= 0) {
                $data['amount_err'] = 'Please enter a valid amount';
            }

            if (empty($data['amount_err'])) {
                if ($this->paymentModel->create($data)) {
                    redirect('payment/showQR/' . $data['payment_reference']);
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('payment/create', $data);
            }
        } else {
            $data = [
                'amount' => '',
                'description' => '',
                'amount_err' => ''
            ];
            $this->view('payment/create', $data);
        }
    }

    public function showQR($reference) {
        $request = $this->paymentModel->getByReference($reference);
        if (!$request || $request->vendor_id != $_SESSION['vendor_id']) {
            redirect('payment/create');
            return;
        }

        $wallet = $this->walletModel->getWalletByVendor($_SESSION['vendor_id']);
        
        // Generate Stellar URI
        // Format: web+stellar:pay?destination=ADDR&amount=AMT&memo=REF&asset_code=XLM
        $stellarUri = "web+stellar:pay?destination=" . $wallet->stellar_public_key . "&amount=" . $request->amount . "&memo=" . $request->payment_reference . "&asset_code=XLM";

        // Generate QR Code using direct API (v6 compatible)
        $qrCode = new QrCode($stellarUri);
        $qrCode->setEncoding(new Encoding('UTF-8'));
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::Low);
        $qrCode->setSize(300);
        $qrCode->setMargin(10);
        $qrCode->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrBase64 = $result->getDataUri();

        $data = [
            'request' => $request,
            'qr_code' => $qrBase64,
            'stellar_uri' => $stellarUri
        ];

        $this->view('payment/show_qr', $data);
    }

    public function monitor() {
        $requests = $this->paymentModel->getPendingByVendor($_SESSION['vendor_id']);
        $data = [
            'requests' => $requests
        ];
        $this->view('payment/monitor', $data);
    }

    /**
     * AJAX endpoint to check status
     */
    public function checkStatus($reference) {
        $request = $this->paymentModel->getByReference($reference);
        if (!$request) {
            echo json_encode(['status' => 'error']);
            return;
        }

        if ($request->status == 'completed') {
            echo json_encode(['status' => 'paid']);
            return;
        }

        // Verify on blockchain
        $wallet = $this->walletModel->getWalletByVendor($request->vendor_id);
        $payment = $this->stellarService->verifyPayment($wallet->stellar_public_key, $request->payment_reference);

        if ($payment) {
            $this->paymentModel->markAsPaid($request->id);
            // Optionally record in transactions table
            echo json_encode(['status' => 'paid', 'hash' => $payment['hash']]);
        } else {
            echo json_encode(['status' => 'pending']);
        }
    }
}
