<?php

/**
 * Mollie Swish - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieswish_devapp_MetaData()
{
    return Gateway::metaData('Mollie Swish');
}

function mollieswish_devapp_config()
{
    return Gateway::config('Mollie Swish');
}

function mollieswish_devapp_link($params)
{
    return Gateway::link($params, 'swish', 'Swish');
}

function mollieswish_devapp_refund($params)
{
    return Gateway::refund($params);
}
