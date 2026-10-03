<?php

/**
 * Mollie Alma - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliealma_devapp_MetaData()
{
    return Gateway::metaData('Mollie Alma');
}

function molliealma_devapp_config()
{
    return Gateway::config('Mollie Alma');
}

function molliealma_devapp_link($params)
{
    return Gateway::link($params, 'alma', 'Alma');
}

function molliealma_devapp_refund($params)
{
    return Gateway::refund($params);
}
