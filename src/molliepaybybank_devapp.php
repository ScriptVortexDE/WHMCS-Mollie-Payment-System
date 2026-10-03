<?php

/**
 * Mollie Pay by Bank - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliepaybybank_devapp_MetaData()
{
    return Gateway::metaData('Mollie Pay by Bank');
}

function molliepaybybank_devapp_config()
{
    return Gateway::config('Mollie Pay by Bank');
}

function molliepaybybank_devapp_link($params)
{
    return Gateway::link($params, 'paybybank', 'Pay by Bank');
}

function molliepaybybank_devapp_refund($params)
{
    return Gateway::refund($params);
}
