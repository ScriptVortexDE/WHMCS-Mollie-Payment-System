<?php

/**
 * Mollie in3 - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliein3_devapp_MetaData()
{
    return Gateway::metaData('Mollie in3');
}

function molliein3_devapp_config()
{
    return Gateway::config('Mollie in3');
}

function molliein3_devapp_link($params)
{
    return Gateway::link($params, 'in3', 'in3');
}

function molliein3_devapp_refund($params)
{
    return Gateway::refund($params);
}
