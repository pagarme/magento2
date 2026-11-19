<?php

namespace Pagarme\Pagarme\Service\Hub;

use Magento\Framework\HTTP\Client\Curl;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Psr\Log\LoggerInterface;

class HubAuthenticationStatusClient implements AuthenticationStatusProviderInterface
{
    const STATUS_URL = 'https://hubapi.pagar.me/v2/management/authentications/status/';
    const TIMEOUT_IN_SECONDS = 5;
    const HTTP_OK = 200;
    const HTTP_NOT_FOUND = 404;

    /** @var Curl */
    private $curl;

    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Curl $curl, Config $config, LoggerInterface $logger)
    {
        $this->curl = $curl;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus($transactionId)
    {
        $this->request($transactionId);

        $httpStatus = (int) $this->curl->getStatus();
        $body = $this->decodeBody();

        if ($httpStatus === self::HTTP_NOT_FOUND) {
            $this->logHubError($httpStatus, $body);
            throw new AuthenticationNotFoundException(
                sprintf('Hub does not know the 3DS authentication %s', $transactionId)
            );
        }

        if ($httpStatus !== self::HTTP_OK) {
            $this->logHubError($httpStatus, $body);
            throw new HubUnavailableException(sprintf('Hub answered HTTP %d to the 3DS status request', $httpStatus));
        }

        return $this->extractStatus($transactionId, $body);
    }

    private function request($transactionId)
    {
        try {
            $this->curl->setTimeout(self::TIMEOUT_IN_SECONDS);
            $this->curl->setCredentials($this->config->getSecretKey(), '');
            $this->curl->addHeader('X-Hub-Environment', $this->config->isSandboxMode() ? 'test' : 'live');
            $this->curl->get(self::STATUS_URL . rawurlencode($transactionId));
        } catch (\Exception $exception) {
            throw new HubUnavailableException(
                'The 3DS status request to the Hub failed: ' . $exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * @return array
     */
    private function decodeBody()
    {
        $decoded = json_decode((string) $this->curl->getBody(), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function extractStatus($transactionId, array $body)
    {
        $answeredTransactionId = $body['tds_server_trans_id'] ?? null;
        if ($answeredTransactionId !== $transactionId) {
            throw new HubUnavailableException('Hub answered for a different 3DS transaction');
        }

        $status = $body['trans_status'] ?? '';
        if (!is_string($status) || $status === '') {
            throw new HubUnavailableException('Hub answer has no 3DS status');
        }

        return $status;
    }

    private function logHubError($httpStatus, array $body)
    {
        $this->logger->error('[Pagarme 3DS] Hub rejected the status request', [
            'http_status' => $httpStatus,
            'code' => $body['error']['code'] ?? null,
            'message' => $body['error']['message'] ?? null,
            'request_id' => $body['error']['request_id'] ?? null,
        ]);
    }
}
