<?php

namespace ScriptVortex\WhmcsMollie;

use WHMCS\Database\Capsule;

/**
 * Shared logic for all Mollie payment gateway modules.
 */
class Gateway
{
    /** Methods that require order lines and a billing address. */
    private const PAY_LATER_METHODS = ['alma', 'billie', 'in3', 'klarna', 'riverty'];

    /** Currencies that Mollie handles without decimals. */
    private const ZERO_DECIMAL_CURRENCIES = ['ISK', 'JPY'];

    /** Number of automatic status checks on the return page before giving up. */
    private const MAX_STATUS_CHECKS = 20;

    public static function metaData(string $displayName): array
    {
        return [
            'DisplayName' => $displayName,
            'APIVersion' => '1.1',
            'DisableLocalCreditCardInput' => true,
            'TokenisedStorage' => false,
        ];
    }

    public static function config(string $displayName): array
    {
        return [
            'FriendlyName' => [
                'Type' => 'System',
                'Value' => $displayName,
            ],
            'key' => [
                'FriendlyName' => 'Live API key',
                'Type' => 'password',
                'Size' => '40',
                'Description' => 'Starts with <code>live_</code>. Found in the Mollie Dashboard under Developers &gt; API keys.',
            ],
            'testkey' => [
                'FriendlyName' => 'Test API key',
                'Type' => 'password',
                'Size' => '40',
                'Description' => 'Starts with <code>test_</code>. Only used when test mode is enabled.',
            ],
            'testmode' => [
                'FriendlyName' => 'Test mode',
                'Type' => 'yesno',
                'Description' => 'Create test payments with the test API key. No real money is transferred.',
            ],
        ];
    }

    /**
     * Client area output: payment button, start of a payment, or the return page.
     */
    public static function link(array $params, ?string $method, string $label): string
    {
        $lang = Lang::strings(Lang::language($params));

        try {
            Transactions::ensureSchema();

            if (isset($_GET['check_payment']) && ctype_digit((string) $_GET['check_payment'])) {
                return self::returnPage($params, (int) $_GET['check_payment'], $lang);
            }

            if (self::shouldStartPayment($params)) {
                return self::startPayment($params, $method, $label, $lang);
            }
        } catch (\Throwable $e) {
            self::logError($params, 'Client area', $e);

            return '<div class="alert alert-danger">' . self::e($lang['errorStartPayment']) . '</div>';
        }

        return self::button($params, $method, $label, $lang);
    }

    /**
     * Refunds (part of) a payment through the Mollie API.
     */
    public static function refund(array $params): array
    {
        try {
            $paymentId = (string) $params['transid'];
            $row = Transactions::findByPaymentId($paymentId);
            $testMode = $row !== null ? (bool) $row->testmode : self::isTestMode($params);

            $refund = self::client($params, $testMode)->createRefund($paymentId, [
                'amount' => [
                    'currency' => strtoupper((string) $params['currency']),
                    'value' => self::formatAmount((float) $params['amount'], (string) $params['currency']),
                ],
                'description' => self::truncate('Refund invoice #' . ($params['invoiceid'] ?? ''), 255),
            ]);

            return [
                'status' => 'success',
                'rawdata' => $refund,
                'transid' => $refund['id'] ?? '',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'rawdata' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handles a Mollie webhook call. Returns the HTTP status code to respond with.
     */
    public static function handleWebhook(string $paymentId): int
    {
        Transactions::ensureSchema();

        $row = Transactions::findByPaymentId($paymentId);

        if ($row === null) {
            // Not created by this module (or by another system on the same Mollie account).
            logTransaction('Mollie', ['id' => $paymentId], 'Webhook - Unknown payment (ignored)');

            return 200;
        }

        $gatewayModule = self::gatewayForRow($row);
        $gateway = getGatewayVariables($gatewayModule);

        if (empty($gateway['type'])) {
            logTransaction('Mollie', ['id' => $paymentId, 'gateway' => $gatewayModule], 'Webhook - Gateway module not activated');

            // Mollie retries the webhook, so the payment is processed once the module is active again.
            return 503;
        }

        try {
            $payment = self::client($gateway, (bool) $row->testmode)->getPayment($paymentId);
            self::applyPayment($row, $payment, $gateway, 'Webhook');
        } catch (\Throwable $e) {
            self::logError($gateway, 'Webhook', $e, ['id' => $paymentId]);

            return 500;
        }

        return 200;
    }

    private static function shouldStartPayment(array $params): bool
    {
        $gateway = (string) $params['paymentmethod'];

        if (($_POST['mollie_start'] ?? '') === $gateway) {
            return true;
        }

        // Order completed in the cart: forward the customer to Mollie directly.
        if (($_GET['a'] ?? '') === 'complete' && basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'cart.php') {
            return true;
        }

        // Add funds / mass payment create an invoice and continue with the chosen gateway.
        return in_array($_GET['action'] ?? '', ['addfunds', 'masspay'], true)
            && ($_POST['paymentmethod'] ?? '') === $gateway;
    }

    private static function button(array $params, ?string $method, string $label, array $lang): string
    {
        $label = $lang['methods'][$method ?? ''] ?? $label;

        $html = '<form method="post" action="' . self::e(self::invoiceUrl($params)) . '">';
        $html .= '<input type="hidden" name="mollie_start" value="' . self::e((string) $params['paymentmethod']) . '">';

        if (function_exists('generate_token')) {
            $html .= generate_token('form');
        }

        $html .= '<button type="submit" class="btn btn-success">' . self::e(sprintf($lang['payWith'], $label)) . '</button>';
        $html .= '</form>';

        if (self::isTestMode($params)) {
            $html .= '<p class="text-warning small">' . self::e($lang['testMode']) . '</p>';
        }

        return $html;
    }

    private static function startPayment(array $params, ?string $method, string $label, array $lang): string
    {
        $gateway = (string) $params['paymentmethod'];
        $apiKey = self::apiKey($params, self::isTestMode($params));
        $testMode = strncmp($apiKey, 'test_', 5) === 0;
        $client = new ApiClient($apiKey);

        $amount = round((float) $params['amount'], 2);
        $currency = strtoupper((string) $params['currency']);
        $currencyId = (int) Capsule::table('tblcurrencies')->where('code', $currency)->value('id');

        try {
            $existing = Transactions::findReusable((int) $params['invoiceid'], $gateway, $amount, $currencyId, $testMode);

            if ($existing !== null) {
                $payment = $client->getPayment($existing->paymentid);
                $checkoutUrl = $payment['_links']['checkout']['href'] ?? null;

                if (($payment['status'] ?? '') === 'open' && $checkoutUrl) {
                    return self::redirect($checkoutUrl);
                }
            }

            $transactionId = Transactions::create([
                'amount' => $amount,
                'currencyid' => $currencyId,
                'ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'userid' => (int) ($params['clientdetails']['userid'] ?? $params['clientdetails']['id'] ?? 0),
                'invoiceid' => (int) $params['invoiceid'],
                'method' => $method ?? 'checkout',
                'gateway' => $gateway,
                'testmode' => $testMode ? 1 : 0,
            ]);

            $payment = $client->createPayment(
                self::paymentData($params, $method, $transactionId, $amount, $currency),
                'whmcs-' . $transactionId
            );

            Transactions::setPaymentId($transactionId, (string) $payment['id']);

            return self::redirect($payment['_links']['checkout']['href'] ?? self::returnUrl($params, $transactionId));
        } catch (\Throwable $e) {
            self::logError($params, 'Start payment', $e, ['invoiceid' => $params['invoiceid']]);

            return '<div class="alert alert-danger">' . self::e($lang['errorStartPayment']) . '</div>'
                . self::button($params, $method, $label, $lang);
        }
    }

    private static function paymentData(array $params, ?string $method, int $transactionId, float $amount, string $currency): array
    {
        $description = trim((string) ($params['description'] ?? ''));

        $data = [
            'amount' => [
                'currency' => $currency,
                'value' => self::formatAmount($amount, $currency),
            ],
            'description' => self::truncate($description !== '' ? $description : 'Invoice #' . $params['invoiceid'], 255),
            'redirectUrl' => self::returnUrl($params, $transactionId),
            'cancelUrl' => self::invoiceUrl($params),
            'webhookUrl' => self::systemUrl($params) . 'modules/gateways/mollie/callback.php',
            'metadata' => [
                'invoice_id' => (int) $params['invoiceid'],
                'whmcs_transaction_id' => $transactionId,
            ],
        ];

        $locale = Lang::mollieLocale(Lang::language($params));

        if ($locale !== null) {
            $data['locale'] = $locale;
        }

        if ($method !== null) {
            $data['method'] = $method;
        }

        if ($method === 'banktransfer') {
            $data['dueDate'] = date('Y-m-d', strtotime('+100 days'));
        }

        if (in_array($method, self::PAY_LATER_METHODS, true)) {
            $data['lines'] = [[
                'description' => $data['description'],
                'quantity' => 1,
                'unitPrice' => $data['amount'],
                'totalAmount' => $data['amount'],
            ]];
            $data['billingAddress'] = self::billingAddress($params['clientdetails'] ?? []);
        }

        return $data;
    }

    private static function billingAddress(array $client): array
    {
        $street = trim(($client['address1'] ?? '') . ' ' . ($client['address2'] ?? ''));

        $address = [
            'givenName' => $client['firstname'] ?? '',
            'familyName' => $client['lastname'] ?? '',
            'organizationName' => $client['companyname'] ?? '',
            'email' => $client['email'] ?? '',
            'streetAndNumber' => $street,
            'postalCode' => $client['postcode'] ?? '',
            'city' => $client['city'] ?? '',
            'region' => $client['state'] ?? '',
            'country' => $client['countrycode'] ?? ($client['country'] ?? ''),
        ];

        return array_filter($address, function ($value) {
            return trim((string) $value) !== '';
        });
    }

    private static function returnPage(array $params, int $transactionId, array $lang): string
    {
        $row = Transactions::find($transactionId);

        if ($row === null || (int) $row->invoiceid !== (int) $params['invoiceid']) {
            return '<p>' . self::e($lang['errorTransactionNotFound']) . '</p>';
        }

        $payment = null;

        // Don't wait for the webhook: ask Mollie for the current status right away.
        if ($row->status === 'open' && !empty($row->paymentid)) {
            $gateway = self::gatewayParamsForRow($row, $params);

            try {
                $payment = self::client($gateway, (bool) $row->testmode)->getPayment($row->paymentid);
                self::applyPayment($row, $payment, $gateway, 'Return');
                $row = Transactions::find($transactionId);
            } catch (\Throwable $e) {
                self::logError($gateway, 'Return page', $e, ['id' => $row->paymentid]);
            }
        }

        if ($row->status === 'paid') {
            return self::redirect(self::appendQuery(self::absoluteReturnUrl($params), 'paymentsuccess=true'));
        }

        if ($row->status === 'closed') {
            return self::redirect(self::appendQuery(self::absoluteReturnUrl($params), 'paymentfailed=true'));
        }

        if ($payment !== null && ($payment['method'] ?? '') === 'banktransfer' && !empty($payment['details']['bankAccount'])) {
            return self::bankTransferDetails($payment, $lang);
        }

        $attempt = (int) ($_GET['check_attempt'] ?? 0);

        if ($attempt >= self::MAX_STATUS_CHECKS) {
            return '<p>' . self::e($lang['paymentPending']) . '</p>';
        }

        $reloadUrl = self::appendQuery(self::returnUrl($params, $transactionId), 'check_attempt=' . ($attempt + 1));

        return '<p><img src="' . self::e(self::systemUrl($params)) . 'modules/gateways/mollie/ajax_loader.gif" alt=""><br>'
            . self::e($lang['checkPayment']) . '</p>'
            . '<script>setTimeout(function () { window.location.href = ' . json_encode($reloadUrl, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '; }, 3000);</script>';
    }

    private static function bankTransferDetails(array $payment, array $lang): string
    {
        $details = $payment['details'];
        $rows = [
            $lang['amount'] => ($payment['amount']['value'] ?? '') . ' ' . ($payment['amount']['currency'] ?? ''),
            $lang['bankName'] => $details['bankName'] ?? '',
            $lang['bankAccount'] => $details['bankAccount'] ?? '',
            $lang['bankBic'] => $details['bankBic'] ?? '',
            $lang['reference'] => $details['transferReference'] ?? '',
        ];

        $html = '<div class="mollie-banktransfer"><h4>' . self::e($lang['bankTransferTitle']) . '</h4>';
        $html .= '<p>' . self::e($lang['bankTransferInstructions']) . '</p><table class="table table-sm">';

        foreach ($rows as $name => $value) {
            if ($value !== '') {
                $html .= '<tr><th>' . self::e($name) . '</th><td>' . self::e($value) . '</td></tr>';
            }
        }

        return $html . '</table></div>';
    }

    /**
     * Applies the Mollie payment status to the local transaction and the WHMCS invoice.
     */
    private static function applyPayment(object $row, array $payment, array $gateway, string $source): void
    {
        $status = (string) ($payment['status'] ?? '');
        $logData = [
            'source' => $source,
            'invoiceid' => $row->invoiceid,
            'paymentid' => $payment['id'] ?? $row->paymentid,
            'status' => $status,
            'method' => $payment['method'] ?? null,
            'amount' => $payment['amount'] ?? null,
            'amountRefunded' => $payment['amountRefunded'] ?? null,
            'amountChargedBack' => $payment['amountChargedBack'] ?? null,
            'mode' => $payment['mode'] ?? null,
        ];
        $gatewayName = (string) ($gateway['name'] ?? $gateway['paymentmethod'] ?? 'Mollie');

        if ($row->status !== 'open') {
            // Later webhook calls for refunds or chargebacks of an already processed payment.
            if ($source === 'Webhook') {
                logTransaction($gatewayName, $logData, 'Webhook - Status update (already processed)');
            }

            return;
        }

        if ($status === 'paid') {
            if (!Transactions::transition((int) $row->id, 'paid')) {
                return; // Processed by a concurrent request.
            }

            try {
                self::addInvoicePayment($row, $payment, $gateway);
            } catch (\Throwable $e) {
                Transactions::reopen((int) $row->id);

                throw $e;
            }

            logTransaction($gatewayName, $logData, $source . ' - Successful (Paid)');

            return;
        }

        if (in_array($status, ['canceled', 'expired', 'failed'], true)) {
            if (Transactions::transition((int) $row->id, 'closed')) {
                logTransaction($gatewayName, $logData, $source . ' - Closed (' . ucfirst($status) . ')');
            }
        }

        // "open", "pending" and "authorized": wait for the next status change.
    }

    private static function addInvoicePayment(object $row, array $payment, array $gateway): void
    {
        if (!function_exists('addInvoicePayment')) {
            require_once ROOTDIR . '/includes/invoicefunctions.php';
        }

        $paymentId = (string) $payment['id'];
        $invoice = Capsule::table('tblinvoices')->where('id', $row->invoiceid)->first();

        if ($invoice === null) {
            throw new \RuntimeException('Invoice #' . $row->invoiceid . ' not found.');
        }

        if (Capsule::table('tblaccounts')->where('transid', $paymentId)->exists()) {
            return; // Already booked.
        }

        $amount = (float) $payment['amount']['value'];
        $paymentCurrencyId = (int) Capsule::table('tblcurrencies')->where('code', $payment['amount']['currency'])->value('id');
        $clientCurrency = getCurrency($invoice->userid);
        $clientCurrencyId = (int) ($clientCurrency['id'] ?? 0);

        // The gateway may be set to convert to another currency than the client's.
        if ($paymentCurrencyId > 0 && $clientCurrencyId > 0 && $paymentCurrencyId !== $clientCurrencyId) {
            $amount = convertCurrency($amount, $paymentCurrencyId, $clientCurrencyId);
        }

        addInvoicePayment((int) $invoice->id, $paymentId, $amount, 0, (string) $gateway['paymentmethod']);
    }

    private static function gatewayForRow(object $row): string
    {
        if (!empty($row->gateway)) {
            return (string) $row->gateway;
        }

        // Transactions created by the original 0100Dev module.
        $method = (string) ($row->method ?? '');

        return 'mollie' . ($method !== '' ? $method : 'checkout') . '_devapp';
    }

    private static function gatewayParamsForRow(object $row, array $params): array
    {
        $gatewayModule = self::gatewayForRow($row);

        if ($gatewayModule === ($params['paymentmethod'] ?? null)) {
            return $params;
        }

        if (!function_exists('getGatewayVariables')) {
            require_once ROOTDIR . '/includes/gatewayfunctions.php';
        }

        $gateway = getGatewayVariables($gatewayModule);

        return !empty($gateway['type']) ? $gateway : $params;
    }

    private static function client(array $params, bool $testMode): ApiClient
    {
        return new ApiClient(self::apiKey($params, $testMode));
    }

    private static function apiKey(array $params, bool $testMode): string
    {
        $liveKey = trim((string) ($params['key'] ?? ''));
        $testKey = trim((string) ($params['testkey'] ?? ''));

        if (!$testMode) {
            return $liveKey;
        }

        if ($testKey !== '') {
            return $testKey;
        }

        // Older setups stored a test key in the (single) API key field. Never fall
        // back to a live key while test mode is on, that would create real payments.
        return strncmp($liveKey, 'test_', 5) === 0 ? $liveKey : '';
    }

    private static function isTestMode(array $params): bool
    {
        return in_array($params['testmode'] ?? '', ['on', '1', 1, true], true);
    }

    private static function formatAmount(float $amount, string $currency): string
    {
        $decimals = in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true) ? 0 : 2;

        return number_format($amount, $decimals, '.', '');
    }

    private static function systemUrl(array $params): string
    {
        return rtrim((string) $params['systemurl'], '/') . '/';
    }

    private static function invoiceUrl(array $params): string
    {
        return self::systemUrl($params) . 'viewinvoice.php?id=' . (int) $params['invoiceid'];
    }

    private static function absoluteReturnUrl(array $params): string
    {
        $url = (string) ($params['returnurl'] ?? '');

        if ($url === '') {
            return self::invoiceUrl($params);
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = self::systemUrl($params) . ltrim($url, '/');
        }

        return $url;
    }

    private static function returnUrl(array $params, int $transactionId): string
    {
        return self::appendQuery(self::absoluteReturnUrl($params), 'check_payment=' . $transactionId);
    }

    private static function appendQuery(string $url, string $query): string
    {
        return $url . (strpos($url, '?') === false ? '?' : '&') . $query;
    }

    private static function redirect(string $url): string
    {
        if (!headers_sent()) {
            header('Location: ' . $url, true, 303);
            exit;
        }

        return '<script>window.location.href = ' . json_encode($url, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . ';</script>'
            . '<p><a href="' . self::e($url) . '">' . self::e($url) . '</a></p>';
    }

    private static function truncate(string $value, int $length): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
    }

    private static function logError(array $params, string $context, \Throwable $e, array $data = []): void
    {
        $data['error'] = $e->getMessage();

        if ($e instanceof ApiException && $e->getField() !== null) {
            $data['field'] = $e->getField();
        }

        logTransaction((string) ($params['name'] ?? $params['paymentmethod'] ?? 'Mollie'), $data, $context . ' - Error');
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
