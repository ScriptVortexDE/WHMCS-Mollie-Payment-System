<?php

/**
 * Mollie Credit Card - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliecreditcard_devapp_MetaData()
{
    return Gateway::metaData('Mollie Credit Card');
}

function molliecreditcard_devapp_config()
{
    return Gateway::config('Mollie Credit Card');
}

function molliecreditcard_devapp_link($params)
{
    return Gateway::link($params, 'creditcard', 'Credit Card');
}

function molliecreditcard_devapp_refund($params)
{
    return Gateway::refund($params);
}
