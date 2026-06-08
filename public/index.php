<?php
/**
 * Entry point for the Street Vendor Point-of-Sale Wallet
 */

require_once '../config/config.php';
require_once '../vendor/autoload.php';
require_once '../core/App.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';
require_once '../core/Model.php';

// Load Helpers
require_once '../app/helpers/session_helper.php';
require_once '../app/helpers/url_helper.php';

$app = new App();
