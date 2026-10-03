<?php

/**
 * Mollie Belfius - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliebelfius_devapp_MetaData()
{
    return Gateway::metaData('Mollie Belfius');
}

function molliebelfius_devapp_config()
{
    return Gateway::config('Mollie Belfius');
}

function molliebelfius_devapp_link($params)
{
    return Gateway::link($params, 'belfius', 'Belfius');
}

function molliebelfius_devapp_refund($params)
{
    return Gateway::refund($params);
}
