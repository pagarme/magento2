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
        $this->mpPaymentHelper = $this->objectManager->get(Payment::class);

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

    private function floatToCentsSplitData($splitData)
    {
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

    private function getTotalPaid($corePlatformOrderDecorator)
    {
        $payments = $corePlatformOrderDecorator->getPaymentMethodCollection();
        $totalPaid = 0;

        foreach ($payments as $payment) {
            $totalPaid += $payment->getAmount();
        }

        return $totalPaid;
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

        $remainder = $this->splitRemainderHander->calculateRemainder(
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

        $splitData = $this->splitRemainderHander->setRemainderToResponsible($remainder, $splitData);

        return $splitData;
    }
}
