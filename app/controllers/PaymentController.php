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
    private $transactionModel;
    private $stellarService;

    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
        $this->paymentModel = $this->model('PaymentRequest');
        $this->walletModel = $this->model('Wallet');
        $this->transactionModel = $this->model('Transaction');
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
                'destination_address' => $wallet->stellar_public_key,
                'amount' => trim($_POST['amount']),
                'description' => trim($_POST['description']),
                'payment_reference' => strtoupper(bin2hex(random_bytes(4))), // Short ref for memo
                'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes')),
                'amount_err' => ''
            ];

            if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
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

        // Use the destination address stored at creation time (qr_data)
        $destination = $request->qr_data;
        
        // Generate Stellar URI (SEP-7)
        // Format: web+stellar:pay?destination=ADDR&amount=AMT&memo=REF&memo_type=MEMO_TEXT
        $stellarUri = "web+stellar:pay?destination=" . urlencode($destination) . 
                      "&amount=" . urlencode($request->amount) . 
                      "&memo=" . urlencode($request->payment_reference) . 
                      "&memo_type=MEMO_TEXT";

        // Generate QR Code
        $qrCode = new QrCode(
            data: $stellarUri,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

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
            echo json_encode(['status' => 'error', 'message' => 'Request not found']);
            return;
        }

        if ($request->status == 'completed') {
            echo json_encode(['status' => 'paid']);
            return;
        }

        if ($request->status == 'expired' || strtotime($request->expires_at) < time()) {
            if ($request->status != 'expired') {
                $this->paymentModel->markAsExpired($request->id);
            }
            echo json_encode(['status' => 'expired']);
            return;
        }

        // Verify on blockchain
        // Use the destination address stored at creation (qr_data)
        $destination = $request->qr_data;
        $payment = $this->stellarService->verifyPayment(
            $destination, 
            $request->payment_reference, 
            $request->amount, 
            $request->asset_code
        );

        if ($payment) {
            // 1. Mark payment request as paid
            $this->paymentModel->markAsPaid($request->id);
            
            // 2. Record in transactions table
            $this->transactionModel->record([
                'vendor_id' => $request->vendor_id,
                'payment_request_id' => $request->id,
                'hash' => $payment['hash'],
                'sender' => $payment['sender'],
                'receiver' => $destination,
                'amount' => $payment['amount'],
                'asset' => $request->asset_code,
                'confirmed_at' => date('Y-m-d H:i:s', strtotime($payment['created_at']))
            ]);

            echo json_encode(['status' => 'paid', 'hash' => $payment['hash']]);
        } else {
            echo json_encode(['status' => 'pending']);
        }
    }
}
