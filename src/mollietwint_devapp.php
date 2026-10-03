<?php

/**
 * Mollie TWINT - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollietwint_devapp_MetaData()
{
    return Gateway::metaData('Mollie TWINT');
}

function mollietwint_devapp_config()
{
    return Gateway::config('Mollie TWINT');
}

function mollietwint_devapp_link($params)
{
    return Gateway::link($params, 'twint', 'TWINT');
}

function mollietwint_devapp_refund($params)
{
    return Gateway::refund($params);
}
