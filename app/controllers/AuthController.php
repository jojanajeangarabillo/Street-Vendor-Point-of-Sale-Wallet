<?php
class AuthController extends Controller {
    private $vendorModel;

    public function __construct() {
        $this->vendorModel = $this->model('Vendor');
    }

    public function register() {
        // Check for POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'phone' => trim($_POST['phone']),
                'csrf_token' => $_POST['csrf_token'] ?? '',
                'full_name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => '',
                'phone_err' => ''
            ];

            // Validate CSRF
            if (!validateCsrfToken($data['csrf_token'])) {
                die('CSRF Token Validation Failed');
            }

            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            } else {
                // Check email
                if ($this->vendorModel->findVendorByEmail($data['email'])) {
                    $data['email_err'] = 'Email is already taken';
                }
            }

            // Validate Name
            if (empty($data['full_name'])) {
                $data['full_name_err'] = 'Please enter name';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            // Validate Confirm Password
            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Please confirm password';
            } else {
                if ($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Passwords do not match';
                }
            }

            // Make sure errors are empty
            if (empty($data['email_err']) && empty($data['full_name_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // Validated
                
                // Hash Password
                $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // Register Vendor
                if ($this->vendorModel->register($data)) {
                    flash('register_success', 'You are registered and can log in');
                    redirect('auth/login');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('auth/register', $data);
            }

        } else {
            // Init data
            $data = [
                'full_name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'phone' => '',
                'full_name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => '',
                'phone_err' => ''
            ];

            // Load view
            $this->view('auth/register', $data);
        }
    }

    public function login() {
        // Check for POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'csrf_token' => $_POST['csrf_token'] ?? '',
                'email_err' => '',
                'password_err' => '',
            ];

            // Validate CSRF
            if (!validateCsrfToken($data['csrf_token'])) {
                die('CSRF Token Validation Failed');
            }

            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            }

            // Validate Password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }

            // Check for vendor/email
            if ($this->vendorModel->findVendorByEmail($data['email'])) {
                // Vendor found
            } else {
                // Vendor not found
                $data['email_err'] = 'No vendor found';
            }

            // Make sure errors are empty
            if (empty($data['email_err']) && empty($data['password_err'])) {
                // Validated
                // Check and set logged in vendor
                $loggedInVendor = $this->vendorModel->login($data['email'], $data['password']);

                if ($loggedInVendor) {
                    // Create Session
                    $this->createUserSession($loggedInVendor);
                } else {
                    $data['password_err'] = 'Password incorrect';
                    $this->view('auth/login', $data);
                }
            } else {
                // Load view with errors
                $this->view('auth/login', $data);
            }

        } else {
            // Init data
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => '',
            ];

            // Load view
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($vendor) {
        session_regenerate_id(true);
        $_SESSION['vendor_id'] = $vendor->id;
        $_SESSION['vendor_email'] = $vendor->email;
        $_SESSION['vendor_name'] = $vendor->full_name;
        redirect('dashboard/index');
    }

    public function logout() {
        unset($_SESSION['vendor_id']);
        unset($_SESSION['vendor_email']);
        unset($_SESSION['vendor_name']);
        session_destroy();
        redirect('auth/login');
    }
}
