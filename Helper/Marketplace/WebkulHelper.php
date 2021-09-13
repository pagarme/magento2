<?php

/**
 * Class AbstractHelper
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Helper\Marketplace;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Webkul\Marketplace\Helper\Payment;
use Webkul\Marketplace\Helper\Data;

class WebkulHelper
{
    private $productCollectionFactory;
    private $salesPerPartnerCollectionFactory;
    private $objectManager;
    private $recipientService;
    private $moduleConfig;

    private $enabled = false;

    public function __construct() {

        $this->objectManager = ObjectManager::getInstance();

        if (!class_exists('Webkul\\Marketplace\\Helper\\Payment')) {
            return null;
        }

        $this->mpPaymentHelper = $this->objectManager->get(Payment::class);

        $this->setEnabled(true);

        $this->recipientService = new RecipientService();
        $this->moneyService = new MoneyService();
        $this->moduleConfig = Magento2CoreSetup::getModuleConfiguration();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getSplitDataFromOrder($platformOrder)
    {
        $orderItems = $platformOrder->getAllItems();
        $splitData['sellers'] = [];
        $splitData['marketplace']['totalCommission'] = 0;
        $totalPaid = $this->moneyService->floatToCents(
            $platformOrder->getSubTotal()
        );

        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            $sellerDetail = $this->mpPaymentHelper->getSellerDetail($productId);
            $sellerId = $sellerDetail['id'];
            $itemPrice = $item->getRowTotal();

            if (!$sellerId) {
                continue;
            }

            $dataForProduct = $this->getProductData(
                $itemPrice,
                $sellerDetail
            );

            if (array_key_exists($sellerId, $splitData['sellers'])) {
                $splitData['sellers'][$sellerId]['commission']
                    += $dataForProduct['sellerCommission'];
                continue;
            }

            $splitData['sellers'][$sellerId] = $dataForProduct;
            $splitData['marketplace']['totalCommission']
                += $dataForProduct['marketplaceCommission'];
        }

        if (empty($splitData['sellers'])) {
            return null;
        }

        $totalSellerCommission = $this
            ->getTotalSellerCommission($splitData['sellers']);
        $totalMarketplaceCommission = $splitData['marketplace']['totalCommission'];
        $remainder = $totalPaid - ($totalSellerCommission
                + $totalMarketplaceCommission);

        if ($remainder == 0) {
            return $splitData;
        }

        $splitData = $this->setRemainderToResponsible($remainder, $splitData);

        $shippingAmount = $this->moneyService->floatToCents(
            $platformOrder->getShippingAmount()
        );

        $splitData['marketplace']['totalCommission'] += $shippingAmount;
        return $splitData;
    }

    private function getProductData($itemPrice, $sellerDetail)
    {
        $percentageCommission = $sellerDetail['commission'] / 100;
        $marketplaceCommission = $itemPrice * $percentageCommission;
        $sellerCommission = $this->moneyService->floatToCents(
            $itemPrice - $marketplaceCommission
        );

        try {
            $recipient = $this->recipientService->findRecipient(
                $sellerDetail['id']
            );
        } catch (\Exception $exception) {
            throw new NotFoundException(__($exception->getMessage()));
        }

        return [
            "marketplaceCommission" => $this->moneyService->floatToCents(
                $marketplaceCommission
            ),
            "sellerCommission" => $sellerCommission,
            "pagarmeId" => $recipient['pagarme_id']
        ];
    }

    private function getTotalSellerCommission(array $sellersData)
    {
        $totalCommission = 0;

        foreach ($sellersData as $commission) {
            $totalCommission += $commission['sellerCommission'];
        }

        return $totalCommission;
    }

    private function setRemainderToResponsible($remainder, $splitData)
    {
        $responsible = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getResponsibilityForReceivingSplitRemainder();

        if ($responsible == 'marketplace') {
            $splitData['marketplace']['totalCommission'] += $remainder;
            return $splitData;
        }

        if ($responsible == 'marketplace_sellers') {
            return $this->divideRemainderBetweenMarkeplaceAndSellers(
                $remainder,
                $splitData
            );
        }

        if ($responsible == 'sellers') {
            return $this->divideRemainderBetweenSellers(
                $remainder,
                $splitData
            );
        }
    }

    private function divideRemainderBetweenMarkeplaceAndSellers(
        $remainder,
        $splitData
    ) {
        $splitData['marketplace']['totalCommission'] += 1;
        $remainder -= 1;
        if ($remainder == 0) {
            return $splitData;
        }

        return $this->divideRemainderBetweenSellers($remainder, $splitData);
    }

    private function divideRemainderBetweenSellers(
        $remainder,
        $splitData
    ) {
        foreach ($splitData['sellers'] as $key => $seller) {
            $seller['sellerCommission'] += 1;
            $remainder -= 1;

            if ($remainder == 0) {
                $splitData['sellers'][$key] = $seller;
                return $splitData;
            }
        }
    }
}
