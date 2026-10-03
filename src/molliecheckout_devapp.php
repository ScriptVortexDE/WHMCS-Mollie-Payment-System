<?php

/**
 * Mollie Checkout - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliecheckout_devapp_MetaData()
{
    return Gateway::metaData('Mollie Checkout');
}

function molliecheckout_devapp_config()
{
    return Gateway::config('Mollie Checkout');
}

function molliecheckout_devapp_link($params)
{
    return Gateway::link($params, null, 'Mollie');
}

function molliecheckout_devapp_refund($params)
{
    return Gateway::refund($params);
}
