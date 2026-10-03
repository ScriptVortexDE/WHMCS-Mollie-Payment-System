<?php

/**
 * Mollie paysafecard - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliepaysafecard_devapp_MetaData()
{
    return Gateway::metaData('Mollie paysafecard');
}

function molliepaysafecard_devapp_config()
{
    return Gateway::config('Mollie paysafecard');
}

function molliepaysafecard_devapp_link($params)
{
    return Gateway::link($params, 'paysafecard', 'paysafecard');
}

function molliepaysafecard_devapp_refund($params)
{
    return Gateway::refund($params);
}
