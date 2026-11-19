<?php

namespace Pagarme\Pagarme\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Service\Hub\AuthenticationNotFoundException;
use Pagarme\Pagarme\Service\Hub\AuthenticationStatusProviderInterface;
use Pagarme\Pagarme\Service\Hub\HubUnavailableException;
use Psr\Log\LoggerInterface;

/**
 * The 3DS result reaches the server through the browser, so the claimed
 * trans_status is confirmed against the Hub before the order is placed.
 * Sandbox stores are not verified.
 */
class ThreeDSAuthenticationStatusValidator
{
    const TRANSACTION_ID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /** @var AuthenticationStatusProviderInterface */
    private $statusProvider;

    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        AuthenticationStatusProviderInterface $statusProvider,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->statusProvider = $statusProvider;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param string|null $authentication JSON the checkout stored in additional_information
     * @throws LocalizedException
     */
    public function assertStatusMatchesHub($authentication)
    {
        if ($this->config->isSandboxMode() || empty($authentication)) {
            return;
        }

        $result = json_decode($authentication, true);
        if (!is_array($result)) {
            throw $this->unverifiable('the authentication payload is not valid JSON');
        }

        $claimedStatus = $result['trans_status'] ?? null;
        if (empty($claimedStatus)) {
            return;
        }

        $transactionId = $result['tds_server_trans_id'] ?? null;
        if (!is_string($transactionId) || !preg_match(self::TRANSACTION_ID_PATTERN, $transactionId)) {
            throw $this->unverifiable('the authentication has no valid tds_server_trans_id');
        }

        $hubStatus = $this->fetchHubStatus($transactionId);
        if ($hubStatus !== $claimedStatus) {
            throw $this->unverifiable(sprintf(
                'status mismatch for %s: checkout sent "%s", Hub has "%s"',
                $transactionId,
                $claimedStatus,
                $hubStatus
            ));
        }
    }

    /**
     * @throws LocalizedException
     */
    private function fetchHubStatus($transactionId)
    {
        try {
            return $this->statusProvider->getStatus($transactionId);
        } catch (AuthenticationNotFoundException $exception) {
            throw $this->unverifiable($exception->getMessage());
        } catch (HubUnavailableException $exception) {
            throw $this->unverifiable($exception->getMessage());
        }
    }

    private function unverifiable($reason)
    {
        $this->logger->error('[Pagarme 3DS] Authentication rejected: ' . $reason);

        return new LocalizedException(
            __('The 3DS authentication could not be verified. Please try again or use another payment method.')
        );
    }
}
