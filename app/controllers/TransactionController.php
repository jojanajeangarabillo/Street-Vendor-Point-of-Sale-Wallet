<?php
class TransactionController extends Controller {
    private $transactionModel;

    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
        $this->transactionModel = $this->model('Transaction');
    }

    public function index() {
        $transactions = $this->transactionModel->getByVendor($_SESSION['vendor_id']);
        $data = [
            'title' => 'Transaction History',
            'transactions' => $transactions
        ];
        $this->view('transaction/index', $data);
    }
}
