<?php

/**
 * Mollie PayPal - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliepaypal_devapp_MetaData()
{
    return Gateway::metaData('Mollie PayPal');
}

function molliepaypal_devapp_config()
{
    return Gateway::config('Mollie PayPal');
}

function molliepaypal_devapp_link($params)
{
    return Gateway::link($params, 'paypal', 'PayPal');
}

function molliepaypal_devapp_refund($params)
{
    return Gateway::refund($params);
}
