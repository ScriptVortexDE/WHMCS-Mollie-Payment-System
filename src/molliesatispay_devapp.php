<?php

/**
 * Mollie Satispay - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliesatispay_devapp_MetaData()
{
    return Gateway::metaData('Mollie Satispay');
}

function molliesatispay_devapp_config()
{
    return Gateway::config('Mollie Satispay');
}

function molliesatispay_devapp_link($params)
{
    return Gateway::link($params, 'satispay', 'Satispay');
}

function molliesatispay_devapp_refund($params)
{
    return Gateway::refund($params);
}
