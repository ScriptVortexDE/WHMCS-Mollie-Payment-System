<?php

/**
 * Loads the Mollie gateway library.
 *
 * The gateway intentionally ships without Composer dependencies, so it can never
 * conflict with the (newer) Guzzle/PSR packages bundled with WHMCS itself.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/ApiException.php';
require_once __DIR__ . '/lib/ApiClient.php';
require_once __DIR__ . '/lib/Lang.php';
require_once __DIR__ . '/lib/Transactions.php';
require_once __DIR__ . '/lib/Gateway.php';
