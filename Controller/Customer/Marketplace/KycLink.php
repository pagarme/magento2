<?php
/**
 * @author      Open Source Team
 * @copyright   2026 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Controller\Customer\Marketplace;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Service\Marketplace\KycLinkService;

/**
 * Class KycLink
 * @package Pagarme\Pagarme\Controller\Customer\Marketplace
 */
class KycLink implements HttpGetActionInterface
{
    /** @var Session */
    private $customerSession;

    /** @var RequestInterface */
    private $request;

    /** @var JsonFactory */
    private $resultJsonFactory;

    /** @var KycLinkService */
    private $kycLinkService;

    /**
     * @param Session $customerSession
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param KycLinkService $kycLinkService
     */
    public function __construct(
        Session $customerSession,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        KycLinkService $kycLinkService
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->kycLinkService = $kycLinkService;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setHttpResponseCode(401)
                ->setData(['message' => __('You must be logged in.')]);
        }

        try {
            $kycLink = $this->kycLinkService->createKycLinkForCustomer(
                (string) $this->request->getParam('id'),
                (int) $this->customerSession->getCustomerId()
            );
        } catch (NoSuchEntityException $e) {
            return $result->setHttpResponseCode(404)
                ->setData(['message' => $e->getMessage()]);
        }

        return $result->setData([
            'url' => $kycLink->url,
            'qr_code' => 'data:image/svg+xml;base64,' . $kycLink->base64_qrcode,
        ]);
    }
}
