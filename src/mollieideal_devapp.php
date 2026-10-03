<?php

/**
 * Mollie iDEAL - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieideal_devapp_MetaData()
{
    return Gateway::metaData('Mollie iDEAL');
}

function mollieideal_devapp_config()
{
    return Gateway::config('Mollie iDEAL');
}

function mollieideal_devapp_link($params)
{
    return Gateway::link($params, 'ideal', 'iDEAL');
}

function mollieideal_devapp_refund($params)
{
    return Gateway::refund($params);
}
