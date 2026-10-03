<?php

/**
 * Mollie MB WAY - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliembway_devapp_MetaData()
{
    return Gateway::metaData('Mollie MB WAY');
}

function molliembway_devapp_config()
{
    return Gateway::config('Mollie MB WAY');
}

function molliembway_devapp_link($params)
{
    return Gateway::link($params, 'mbway', 'MB WAY');
}

function molliembway_devapp_refund($params)
{
    return Gateway::refund($params);
}
