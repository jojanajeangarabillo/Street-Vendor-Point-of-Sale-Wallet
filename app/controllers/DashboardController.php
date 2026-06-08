<?php
class DashboardController extends Controller {
    public function index() {
        $data = [
            'title' => 'Welcome to Street Vendor POS'
        ];
        $this->view('dashboard/index', $data);
    }
}
