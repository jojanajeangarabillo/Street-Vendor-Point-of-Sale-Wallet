<?php
class PaymentController extends Controller {
    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
    }

    public function create() {
        $data = ['title' => 'Create Payment Request'];
        $this->view('payment/create', $data);
    }

    public function monitor() {
        $data = ['title' => 'Payment Monitoring'];
        $this->view('payment/monitor', $data);
    }
}
