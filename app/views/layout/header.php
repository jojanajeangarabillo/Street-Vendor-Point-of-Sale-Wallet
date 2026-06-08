<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/assets/css/style.css">
</head>
<body>

<?php if(isLoggedIn()) : ?>
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?php echo URLROOT; ?>"><?php echo SITENAME; ?></a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav w-100 d-none d-md-block"></div>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="<?php echo URLROOT; ?>/auth/logout">Sign out</a>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'dashboard') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/dashboard">
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'wallet') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/wallet">
                            <i class="bi bi-wallet2 me-2"></i> Wallet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'payment/create') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/payment/create">
                            <i class="bi bi-qr-code me-2"></i> Create Payment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'payment/monitor') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/payment/monitor">
                            <i class="bi bi-display me-2"></i> Monitoring
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'transaction') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/transaction">
                            <i class="bi bi-clock-history me-2"></i> History
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Account</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_GET['url'] ?? '', 'profile') !== false) ? 'active' : ''; ?>" href="<?php echo URLROOT; ?>/profile">
                            <i class="bi bi-person me-2"></i> Profile Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
<?php else : ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container">
        <a class="navbar-brand" href="<?php echo URLROOT; ?>"><?php echo SITENAME; ?></a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="<?php echo URLROOT; ?>/auth/login">Login</a>
            <a class="nav-link" href="<?php echo URLROOT; ?>/auth/register">Register</a>
        </div>
    </div>
</nav>
<div class="container">
<?php endif; ?>
