<?php

/**
 * Mollie MyBank - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliemybank_devapp_MetaData()
{
    return Gateway::metaData('Mollie MyBank');
}

function molliemybank_devapp_config()
{
    return Gateway::config('Mollie MyBank');
}

function molliemybank_devapp_link($params)
{
    return Gateway::link($params, 'mybank', 'MyBank');
}

function molliemybank_devapp_refund($params)
{
    return Gateway::refund($params);
}
