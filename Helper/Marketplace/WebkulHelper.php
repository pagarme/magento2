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
use Pagarme\Pagarme\Helper\Marketplace\SplitRemainderHandler;
use Webkul\Marketplace\Helper\Payment;

class WebkulHelper
{
    private $objectManager;
    private $recipientService;

    private $enabled = false;

    public function __construct()
    {

        $this->objectManager = ObjectManager::getInstance();

        if (!class_exists('Webkul\\Marketplace\\Helper\\Payment')) {
            return null;
        }
        $this->splitRemainderHander = new SplitRemainderHandler();
        $this->mpPaymentHelper = $this->objectManager->get(Payment::class);

        $this->setEnabled(true);

        $this->recipientService = new RecipientService();
        $this->moneyService = new MoneyService();
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
