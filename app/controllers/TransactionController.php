<?php
class TransactionController extends Controller {
    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
    }

    public function index() {
        $data = ['title' => 'Transaction History'];
        $this->view('transaction/index', $data);
    }
}
