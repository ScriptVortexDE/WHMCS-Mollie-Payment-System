<?php

/**
 * Mollie Bizum - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliebizum_devapp_MetaData()
{
    return Gateway::metaData('Mollie Bizum');
}

function molliebizum_devapp_config()
{
    return Gateway::config('Mollie Bizum');
}

function molliebizum_devapp_link($params)
{
    return Gateway::link($params, 'bizum', 'Bizum');
}

function molliebizum_devapp_refund($params)
{
    return Gateway::refund($params);
}
