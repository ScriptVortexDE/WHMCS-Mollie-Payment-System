<?php

/**
 * Mollie Bank Transfer - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliebanktransfer_devapp_MetaData()
{
    return Gateway::metaData('Mollie Bank Transfer');
}

function molliebanktransfer_devapp_config()
{
    return Gateway::config('Mollie Bank Transfer');
}

function molliebanktransfer_devapp_link($params)
{
    return Gateway::link($params, 'banktransfer', 'Bank Transfer');
}

function molliebanktransfer_devapp_refund($params)
{
    return Gateway::refund($params);
}
