<?php

namespace Pagarme\Pagarme\Service\Transaction;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Model\CoreAuth;

class TdsTokenService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->coreAuth = new CoreAuth('');
        $this->config = $config;
    }

    /**
     * Fetch a TDS token from the NX/Auth Switch provider.
     * NX is retrocompatible with legacy format, so frontend only loads NX SDK.
     *
     * @param string $identifier Payment profile ID (pp_*) or account ID (acc_*)
     * @return array{tds_token: string}|array Empty array on failure
     */
    public function getTdsToken($identifier)
    {
        $environment = $this->config->isSandboxMode() ? 'test' : 'live';

        try {
            $token = $this->getTdsTokenFromNxProvider($environment, $identifier);
            if (!empty($token)) {
                return ['tds_token' => $token];
            }
        } catch (\Throwable $e) {
            // NX provider failed; try legacy fallback
        }


        // Legacy
        try {
            $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
            $token = $tdsTokenProxy->getTdsToken($environment, $identifier)->tdsToken;
            if (!empty($token)) {
                return ['tds_token' => $token];
            }
        } catch (\Throwable $e) {
            // Legacy provider also failed
        }

        return [];
    }

    /**
     * Fetch TDS token from NX/Auth Switch provider.
     * Makes a direct HTTP call to /v2/management/tds-token endpoint.
     *
     * @param string $environment 'live' or 'test'
     * @param string $identifier Payment profile ID (pp_*) or account ID (acc_*)
     * @return string|null
     * @throws \Throwable
     */
    private function getTdsTokenFromNxProvider($environment, $identifier)
    {
        $url = 'https://hubapi.pagar.me/v2/management/tds-token';

        $payload = json_encode(['merchant_id' => $identifier]);

        $auth = $this->coreAuth->getHubToken();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($auth . ':'),
            'X-Hub-Environment: ' . $environment
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("HTTP Error: $error");
        }

        if ($httpCode !== 200) {
            throw new \Exception("HTTP Error {$httpCode}: " . substr($response, 0, 200));
        }

        if (empty($response)) {
            throw new \Exception('Empty response from NX provider');
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['tds_token'])) {
            throw new \Exception('Invalid JSON response from NX provider');
        }

        return $decoded['tds_token'];
    }
}
