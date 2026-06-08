<?php
class ProfileController extends Controller {
    public function __construct() {
        if (!isLoggedIn()) redirect('auth/login');
    }

    public function index() {
        $data = ['title' => 'Profile Settings'];
        $this->view('profile/index', $data);
    }
}
