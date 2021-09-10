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
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Webkul\Marketplace\Helper\Payment;
use Webkul\Marketplace\Helper\Data;

class WebkulHelper
{
    private $productCollectionFactory;
    private $salesPerPartnerCollectionFactory;
    private $objectManager;
    private $recipientService;

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
        $totalPaid = (int) $this->moneyService->floatToCents(
            $platformOrder->getGrandTotal()
        );

        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            $sellerDetail = $this->mpPaymentHelper->getSellerDetail($productId);
            $sellerId = $sellerDetail['id'];
            $itemPrice = $item->getRowTotal();

            if(!$sellerId) {
                continue;
            }

            $sellerDataForProduct = $this->getSellerData(
                $itemPrice,
                $sellerDetail
            );

            if (array_key_exists($sellerId, $splitData['sellers'])) {
                $splitData['sellers'][$sellerId]['sellerCommission']
                    += $sellerDataForProduct['sellerCommission'];
                continue;
            }

            $splitData['sellers'][$sellerId] = $sellerDataForProduct;
        }

        if(empty($splitData['sellers'])) {
            return null;
        }

        $marketplaceCommission
            = $totalPaid - $this->getTotalSellerCommission(
                $splitData['sellers']
            );
        $splitData['marketplace']
            = ['marketplaceCommission' => $marketplaceCommission];

        return $splitData;
    }

    private function getSellerData($itemPrice, $sellerDetail)
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
            "sellerCommission" => $sellerCommission,
            "pagarmeId" => $recipient['pagarme_id']
        ];
    }

    private function getTotalSellerCommission(array $splitData)
    {
        $totalCommission = 0;

        foreach ($splitData as $commission) {
            $totalCommission += $commission['sellerCommission'];
        }

        return $totalCommission;
    }
}
