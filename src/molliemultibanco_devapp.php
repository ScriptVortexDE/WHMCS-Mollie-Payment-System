<?php

/**
 * Mollie Multibanco - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliemultibanco_devapp_MetaData()
{
    return Gateway::metaData('Mollie Multibanco');
}

function molliemultibanco_devapp_config()
{
    return Gateway::config('Mollie Multibanco');
}

function molliemultibanco_devapp_link($params)
{
    return Gateway::link($params, 'multibanco', 'Multibanco');
}

function molliemultibanco_devapp_refund($params)
{
    return Gateway::refund($params);
}
