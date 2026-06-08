<?php
class DashboardController extends Controller {
    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Vendor Dashboard'
        ];
        $this->view('dashboard/index', $data);
    }
}
