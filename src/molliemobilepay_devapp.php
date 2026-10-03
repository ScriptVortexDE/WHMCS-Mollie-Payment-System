<?php

/**
 * Mollie MobilePay - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliemobilepay_devapp_MetaData()
{
    return Gateway::metaData('Mollie MobilePay');
}

function molliemobilepay_devapp_config()
{
    return Gateway::config('Mollie MobilePay');
}

function molliemobilepay_devapp_link($params)
{
    return Gateway::link($params, 'mobilepay', 'MobilePay');
}

function molliemobilepay_devapp_refund($params)
{
    return Gateway::refund($params);
}
