<?php

namespace ScriptVortex\WhmcsMollie;

/**
 * Minimal client for the Mollie Payments API v2, based on ext-curl only.
 */
class ApiClient
{
    public const MODULE_VERSION = '2.0.0';

    private const ENDPOINT = 'https://api.mollie.com/v2/';

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = trim($apiKey);
    }

    public function createPayment(array $data, ?string $idempotencyKey = null): array
    {
        return $this->request('POST', 'payments', $data, $idempotencyKey);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', 'payments/' . rawurlencode($paymentId));
    }

    public function createRefund(string $paymentId, array $data): array
    {
        return $this->request('POST', 'payments/' . rawurlencode($paymentId) . '/refunds', $data);
    }

    private function request(string $method, string $path, ?array $body = null, ?string $idempotencyKey = null): array
    {
        if ($this->apiKey === '') {
            throw new ApiException('No Mollie API key configured for this payment gateway.');
        }

        if (!function_exists('curl_init')) {
            throw new ApiException('The PHP cURL extension is required for the Mollie gateway.');
        }

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
            'User-Agent: WHMCS-Mollie-Payment-System/' . self::MODULE_VERSION . ' PHP/' . PHP_VERSION,
        ];

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        $json = null;

        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POSTFIELDS] = $json;
        }

        if ($idempotencyKey !== null) {
            $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
        }

        $options[CURLOPT_HTTPHEADER] = $headers;

        $ch = curl_init(self::ENDPOINT . $path);
        curl_setopt_array($ch, $options);

        $raw = curl_exec($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $curlError = curl_error($ch);

        $this->log($method . ' ' . $path, $json, $raw === false ? $curlError : $raw);

        if ($raw === false) {
            throw new ApiException('Could not connect to Mollie: ' . $curlError);
        }

        $decoded = json_decode((string) $raw, true);

        if ($httpStatus >= 400) {
            $message = is_array($decoded) && isset($decoded['detail'])
                ? (string) $decoded['detail']
                : 'Unexpected HTTP status ' . $httpStatus;

            $field = is_array($decoded) && isset($decoded['field']) ? (string) $decoded['field'] : null;

            throw new ApiException($message, $httpStatus, $field);
        }

        if (!is_array($decoded)) {
            throw new ApiException('Invalid response received from Mollie.', $httpStatus);
        }

        return $decoded;
    }

    private function log(string $action, ?string $request, $response): void
    {
        if (function_exists('logModuleCall')) {
            // The API key is masked in the WHMCS module log.
            logModuleCall('mollie', $action, (string) $request, $response, '', [$this->apiKey]);
        }
    }
}
