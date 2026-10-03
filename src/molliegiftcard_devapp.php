<?php

/**
 * Mollie Gift Card - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliegiftcard_devapp_MetaData()
{
    return Gateway::metaData('Mollie Gift Card');
}

function molliegiftcard_devapp_config()
{
    return Gateway::config('Mollie Gift Card');
}

function molliegiftcard_devapp_link($params)
{
    return Gateway::link($params, 'giftcard', 'Gift Card');
}

function molliegiftcard_devapp_refund($params)
{
    return Gateway::refund($params);
}
