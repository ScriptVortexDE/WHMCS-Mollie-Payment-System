<?php

/**
 * Mollie Bancontact - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliebancontact_devapp_MetaData()
{
    return Gateway::metaData('Mollie Bancontact');
}

function molliebancontact_devapp_config()
{
    return Gateway::config('Mollie Bancontact');
}

function molliebancontact_devapp_link($params)
{
    return Gateway::link($params, 'bancontact', 'Bancontact');
}

function molliebancontact_devapp_refund($params)
{
    return Gateway::refund($params);
}
