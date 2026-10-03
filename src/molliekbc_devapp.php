<?php

/**
 * Mollie KBC/CBC Payment Button - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliekbc_devapp_MetaData()
{
    return Gateway::metaData('Mollie KBC/CBC Payment Button');
}

function molliekbc_devapp_config()
{
    return Gateway::config('Mollie KBC/CBC Payment Button');
}

function molliekbc_devapp_link($params)
{
    return Gateway::link($params, 'kbc', 'KBC/CBC Payment Button');
}

function molliekbc_devapp_refund($params)
{
    return Gateway::refund($params);
}
