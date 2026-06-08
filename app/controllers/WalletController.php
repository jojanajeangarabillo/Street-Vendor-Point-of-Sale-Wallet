<?php
class WalletController extends Controller {
    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
    }

    public function index() {
        $data = ['title' => 'Wallet Management'];
        $this->view('wallet/index', $data);
    }
}
