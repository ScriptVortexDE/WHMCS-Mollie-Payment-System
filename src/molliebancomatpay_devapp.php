<?php

/**
 * Mollie Bancomat Pay - WHMCS payment gateway.
 */

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/mollie/bootstrap.php';

use ScriptVortex\WhmcsMollie\Gateway;

function molliebancomatpay_devapp_MetaData()
{
    return Gateway::metaData('Mollie Bancomat Pay');
}

function molliebancomatpay_devapp_config()
{
    return Gateway::config('Mollie Bancomat Pay');
}

function molliebancomatpay_devapp_link($params)
{
    return Gateway::link($params, 'bancomatpay', 'Bancomat Pay');
}

function molliebancomatpay_devapp_refund($params)
{
    return Gateway::refund($params);
}
