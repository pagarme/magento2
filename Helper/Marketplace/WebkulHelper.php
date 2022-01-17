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

use Magento\Framework\App\ObjectManager as MagentoObjectManager;
use Magento\Framework\Module\Manager as MagentoModuleManager;
use Magento\Framework\Exception\NotFoundException;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Pagarme\Helper\Marketplace\SplitRemainderHandler;
use Webkul\Marketplace\Helper\Payment;

class WebkulHelper
{
    private const MODULE_MARKETPLACE_NAME = 'Webkul_Marketplace';
    private $webkulPaymentHelper;
    private $objectManager;
    private $recipientService;

    private $enabled = false;

    public function __construct()
    {

        $this->objectManager = MagentoObjectManager::getInstance();

        if ($this->isWebkulMarketplaceModuleDisabled()) {
            return;
        }

        $this->splitRemainderHander = new SplitRemainderHandler();
        $this->webkulPaymentHelper = $this->objectManager->get(Payment::class);

        $this->setEnabled(true);

        $this->recipientService = new RecipientService();
        $this->moneyService = new MoneyService();
    }

    private function isWebkulMarketplaceModuleDisabled()
    {
        $moduleManager = $moduleManager =
            $this->objectManager->get(MagentoModuleManager::class);

        return !$moduleManager->isEnabled(self::MODULE_MARKETPLACE_NAME);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    private function getSellerAndCommissions($itemPrice, $productId)
    {
        $sellerDetail = $this->webkulPaymentHelper->getSellerDetail($productId);
        $sellerId = $sellerDetail['id'];

        if (empty($sellerId)) {
            return [];
        }

        $percentageCommission = $sellerDetail['commission'] / 100;
        $marketplaceCommission = $itemPrice * $percentageCommission;
        $sellerCommission = $itemPrice - $marketplaceCommission;

        try {
            $recipient = $this->recipientService->findRecipient(
                $sellerId
            );
        } catch (\Exception $exception) {
            throw new NotFoundException(__($exception->getMessage()));
        }

        return [
            "marketplaceCommission" => $marketplaceCommission,
            "commission" => $sellerCommission,
            "pagarmeId" => $recipient['pagarme_id'],
            "webkulSellerId" => $sellerId
        ];
    }

    private function getTotalPaid($corePlatformOrderDecorator)
    {
        $payments = $corePlatformOrderDecorator->getPaymentMethodCollection();
        $totalPaid = 0;

        foreach ($payments as $payment) {
            $totalPaid += $payment->getAmount();
        }

        return $totalPaid;
    }

    private function getProductTotal($items)
    {
        $total = 0;
        foreach ($items as $item) {
            $total += $item->getRowTotal();
        }

        return $total;
    }

    private function addCommissionsToSplitData($sellerAndCommisions, &$splitData)
    {
        $sellerId = $sellerAndCommisions['webkulSellerId'];

        if (array_key_exists($sellerId, $splitData['sellers'])) {
            $splitData['sellers'][$sellerId]['commission']
                += $sellerAndCommisions['commission'];
            $splitData['marketplace']['totalCommission']
                += $sellerAndCommisions['marketplaceCommission'];

            return;
        }

        $splitData['sellers'][$sellerId] = $sellerAndCommisions;
        $splitData['marketplace']['totalCommission']
            += $sellerAndCommisions['marketplaceCommission'];
    }

    private function handleRemainder(&$splitData, $totalPaidProductWithoutSeller, $totalPaid)
    {
        $remainder = $this->splitRemainderHander->calculateRemainder(
            $splitData,
            $totalPaidProductWithoutSeller,
            $totalPaid
        );

        if ($remainder == 0) {
            return $splitData;
        }

        return $this->splitRemainderHander->setRemainderToResponsible($remainder, $splitData);
    }

    private function handleExtrasAndDiscounts($platformOrderDecorator, &$splitData)
    {
        $items = $platformOrderDecorator->getPlatformOrder()->getAllItems();
        $totalPaid = $this->getTotalPaid($platformOrderDecorator);
        $productTotal = $this->getProductTotal($items);

        $extraOrDiscountTotal = $productTotal - $totalPaid;
    }

    public function getSplitDataFromOrder($corePlatformOrderDecorator)
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $platformOrder = $corePlatformOrderDecorator->getPlatformOrder();
        $orderItems = $platformOrder->getAllItems();
        $splitData['sellers'] = [];
        $splitData['marketplace']['totalCommission'] = 0;
        $totalPaidProductWithoutSeller = 0;

        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            $itemPrice = $this->moneyService->floatToCents($item->getRowTotal());

            $sellerAndCommisions = $this->getSellerAndCommissions(
                $itemPrice,
                $productId
            );

            if (empty($sellerAndCommisions)) {
                $totalPaidProductWithoutSeller += $itemPrice;
                continue;
            }

            $this->addCommissionsToSplitData($sellerAndCommisions, $splitData);
        }

        if (empty($splitData['sellers'])) {
            return null;
        }

        $splitData['marketplace']['totalCommission']
            += $totalPaidProductWithoutSeller;

        //TODO: handle extras and discounts;
        $shippingAmount = $this->moneyService->floatToCents(
            $platformOrder->getShippingAmount()
        );
        $splitData['marketplace']['totalCommission'] += $shippingAmount;

        $totalPaid = $this->getTotalPaid($corePlatformOrderDecorator);
        return $this->handleRemainder($splitData, $totalPaidProductWithoutSeller, $totalPaid);
    }
}
