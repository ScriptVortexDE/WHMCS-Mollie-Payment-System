<?php

/**
 * Mollie Apple Pay - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function mollieapplepay_devapp_MetaData()
{
    return Gateway::metaData('Mollie Apple Pay');
}

function mollieapplepay_devapp_config()
{
    return Gateway::config('Mollie Apple Pay');
}

function mollieapplepay_devapp_link($params)
{
    return Gateway::link($params, 'applepay', 'Apple Pay');
}

function mollieapplepay_devapp_refund($params)
{
    return Gateway::refund($params);
}
