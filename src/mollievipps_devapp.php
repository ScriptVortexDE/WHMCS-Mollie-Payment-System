<?php

/**
 * Mollie Vipps - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollievipps_devapp_MetaData()
{
    return Gateway::metaData('Mollie Vipps');
}

function mollievipps_devapp_config()
{
    return Gateway::config('Mollie Vipps');
}

function mollievipps_devapp_link($params)
{
    return Gateway::link($params, 'vipps', 'Vipps');
}

function mollievipps_devapp_refund($params)
{
    return Gateway::refund($params);
}
