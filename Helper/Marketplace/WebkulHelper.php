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
use Magento\Framework\Exception\NotFoundException;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Webkul\Marketplace\Helper\Payment;
use Webkul\Marketplace\Helper\Data;

class WebkulHelper
{
    const ONLY_MARKETPLACE = 'marketplace';
    const MARKETPLACE_SELLERS = 'marketplace_sellers';
    const ONLY_SELLERS = 'sellers';

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

    public function getTotalPaid($corePlatformOrderDecorator){
        $payments = $corePlatformOrderDecorator->getPaymentMethodCollection();
        $totalPaid = 0;

        foreach ($payments as $payment){
            $totalPaid += $payment->getAmount();
        }

        return $totalPaid;
    }

    public function getSplitDataFromOrder($corePlatformOrderDecorator)
    {
        $platformOrder = $corePlatformOrderDecorator->getPlatformOrder();
        $orderItems = $platformOrder->getAllItems();
        $splitData['sellers'] = [];
        $splitData['marketplace']['totalCommission'] = 0;
        $totalPaid = $this->getTotalPaid($corePlatformOrderDecorator);
        $totalPaidProductWithoutSeller = 0;

        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            $sellerDetail = $this->mpPaymentHelper->getSellerDetail($productId);
            $sellerId = $sellerDetail['id'];
            $itemPrice = $item->getRowTotal();

            if (!$sellerId) {
                $totalPaidProductWithoutSeller += $itemPrice;
                continue;
            }

            $dataForProduct = $this->getProductData(
                $itemPrice,
                $sellerDetail
            );

            if (array_key_exists($sellerId, $splitData['sellers'])) {
                $splitData['sellers'][$sellerId]['commission']
                    += $dataForProduct['commission'];
                $splitData['marketplace']['totalCommission']
                    += $dataForProduct['marketplaceCommission'];

                continue;
            }

            $splitData['sellers'][$sellerId] = $dataForProduct;
            $splitData['marketplace']['totalCommission']
                += $dataForProduct['marketplaceCommission'];
        }

        if (empty($splitData['sellers'])) {
            return null;
        }

        $splitData = $this->floatToCentsSplitData($splitData);

        $totalPaidProductWithoutSeller = $this->moneyService->floatToCents(
            $totalPaidProductWithoutSeller
        );

        $remainder = $this->calculateRemainder(
            $splitData,
            $totalPaidProductWithoutSeller,
            $totalPaid
        );

        $shippingAmount = $this->moneyService->floatToCents(
            $platformOrder->getShippingAmount()
        );
        $splitData['marketplace']['totalCommission'] += $shippingAmount;
        $splitData['marketplace']['totalCommission']
            += $totalPaidProductWithoutSeller;

        if ($remainder == 0) {
            return $splitData;
        }

        $splitData = $this->setRemainderToResponsible($remainder, $splitData);

        return $splitData;
    }

    private function getProductData($itemPrice, $sellerDetail)
    {
        $percentageCommission = $sellerDetail['commission'] / 100;
        $marketplaceCommission = $itemPrice * $percentageCommission;
        $sellerCommission = $itemPrice - $marketplaceCommission;


        try {
            $recipient = $this->recipientService->findRecipient(
                $sellerDetail['id']
            );
        } catch (\Exception $exception) {
            throw new NotFoundException(__($exception->getMessage()));
        }

        return [
            "marketplaceCommission" => $marketplaceCommission,
            "commission" => $sellerCommission,
            "pagarmeId" => $recipient['pagarme_id']
        ];
    }

    private function getTotalSellerCommission(array $sellersData)
    {
        $totalCommission = 0;

        foreach ($sellersData as $commission) {
            $totalCommission += $commission['commission'];
        }

        return $totalCommission;
    }

    private function setRemainderToResponsible($remainder, $splitData)
    {
        $responsible = $this->moduleConfig
            ->getMarketplaceConfig()
            ->getResponsibilityForReceivingSplitRemainder();

        switch ($responsible) {
            case self::ONLY_MARKETPLACE:
                $splitData['marketplace']['totalCommission'] += $remainder;
                return $splitData;
            case self::ONLY_SELLERS:
                return $this->divideRemainderBetweenSellers(
                    $remainder,
                    $splitData
                );
            case self::MARKETPLACE_SELLERS:
                return $this->divideRemainderBetweenMarkeplaceAndSellers(
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
            $seller['commission'] += 1;
            $remainder -= 1;

            if ($remainder == 0) {
                $splitData['sellers'][$key] = $seller;
                return $splitData;
            }

            $splitData['sellers'][$key] = $seller;
        }

        return $this->divideRemainderBetweenMarkeplaceAndSellers(
            $remainder,
            $splitData
        );
    }

    private function floatToCentsSplitData($splitData) {
        foreach ($splitData['sellers'] as $key => $data) {
            $splitData['sellers'][$key]['commission']
                = $this->moneyService->floatToCents($data['commission']);
        }

        $splitData['marketplace']['totalCommission']
            = $this->moneyService->floatToCents(
                $splitData['marketplace']['totalCommission']
        );

        return $splitData;
    }

    private function calculateRemainder(
        $splitData,
        $totalPaidProductWithoutSeller,
        $totalPaid
    ) {
        $totalSellerCommission
            = $this->getTotalSellerCommission($splitData['sellers']);

        $totalMarketplaceCommission
            = $splitData['marketplace']['totalCommission'];

        $remainder = $totalPaid - $totalPaidProductWithoutSeller
            - $totalSellerCommission - $totalMarketplaceCommission;

        return $remainder;
    }
}
