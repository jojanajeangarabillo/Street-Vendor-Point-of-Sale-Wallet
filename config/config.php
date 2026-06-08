<?php
/**
 * Application Configuration
 */

// Define URL root
define('URLROOT', 'http://localhost/Street-Vendor-Point-of-Sale-Wallet');

// App Name
define('SITENAME', 'Street Vendor POS');

// App Version
define('APPVERSION', '1.0.0');

// App Root
define('APPROOT', dirname(dirname(__FILE__)) . '/app');

// Database Params
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'street_vendor_pos');

// Stellar Configuration
define('STELLAR_NETWORK', 'testnet'); // 'testnet' or 'public'
define('STELLAR_HORIZON_URL', 'https://horizon-testnet.stellar.org');
define('STELLAR_FRIENDBOT_URL', 'https://friendbot.stellar.org');
define('STELLAR_NETWORK_PASSPHRASE', 'Test SDF Network ; September 2015');
