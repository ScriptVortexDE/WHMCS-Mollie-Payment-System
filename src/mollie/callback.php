<?php

/**
 * Mollie webhook endpoint.
 *
 * Mollie only posts the payment id; the actual status is always fetched from
 * the Mollie API, so the request itself does not need to be trusted.
 */

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once __DIR__ . '/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

$paymentId = trim((string) ($_POST['id'] ?? ''));

if (!preg_match('/^tr_[A-Za-z0-9]+$/', $paymentId)) {
    logTransaction('Mollie', $_POST, 'Webhook - Invalid request');
    http_response_code(400);
    exit;
}

http_response_code(Gateway::handleWebhook($paymentId));
exit;
