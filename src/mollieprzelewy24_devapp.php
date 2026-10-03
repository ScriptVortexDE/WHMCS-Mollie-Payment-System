<?php

/**
 * Mollie Przelewy24 - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieprzelewy24_devapp_MetaData()
{
    return Gateway::metaData('Mollie Przelewy24');
}

function mollieprzelewy24_devapp_config()
{
    return Gateway::config('Mollie Przelewy24');
}

function mollieprzelewy24_devapp_link($params)
{
    return Gateway::link($params, 'przelewy24', 'Przelewy24');
}

function mollieprzelewy24_devapp_refund($params)
{
    return Gateway::refund($params);
}
