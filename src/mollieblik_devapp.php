<?php

/**
 * Mollie BLIK - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieblik_devapp_MetaData()
{
    return Gateway::metaData('Mollie BLIK');
}

function mollieblik_devapp_config()
{
    return Gateway::config('Mollie BLIK');
}

function mollieblik_devapp_link($params)
{
    return Gateway::link($params, 'blik', 'BLIK');
}

function mollieblik_devapp_refund($params)
{
    return Gateway::refund($params);
}
