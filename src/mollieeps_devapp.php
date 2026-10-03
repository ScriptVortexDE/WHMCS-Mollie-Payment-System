<?php

/**
 * Mollie EPS - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieeps_devapp_MetaData()
{
    return Gateway::metaData('Mollie EPS');
}

function mollieeps_devapp_config()
{
    return Gateway::config('Mollie EPS');
}

function mollieeps_devapp_link($params)
{
    return Gateway::link($params, 'eps', 'EPS');
}

function mollieeps_devapp_refund($params)
{
    return Gateway::refund($params);
}
