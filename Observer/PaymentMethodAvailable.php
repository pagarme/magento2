<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var \Mundipagg\Core\Kernel\Aggregates\Configuration
     */
    protected $mundipaggConfig;
    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    public function __construct(
        RecurrenceProductHelper $recurrenceProductHelper
    ) {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->mundipaggConfig = Magento2CoreSetup::getModuleConfiguration();
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();

        if (!$quote) {
            return;
        }

        $recurrenceProduct = $this->getRecurrenceProducts($quote);
        if (!empty($recurrenceProduct)) {

            $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $isMundipaggMethod = strpos($currentMethod, "mundipagg");

            if (
                !$this->mundipaggConfig->isEnabled() ||
                $isMundipaggMethod === false
            ) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
                return;
            }

            $this->switchPaymentMethodsForRecurrence($observer, $recurrenceProduct);
        }

        if (!$this->mundipaggConfig->isEnabled()) {
            $this->disableMundipaggPaymentMethods($observer);
        }

        return;
    }

    /**
     * @param Observer $observer
     */
    private function disableMundipaggPaymentMethods(Observer $observer)
    {
        $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();

        $paymentMethodAvaliable = $this->getAvailableConfigMethods();

        if (in_array($currentMethod, $paymentMethodAvaliable)) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }

    /**
     * @param $observer
     * @param $recurrenceProducts
     */
    private function switchPaymentMethodsForRecurrence($observer, $recurrenceProducts)
    {
        $mundipaggPaymentsMethods = $this->getAvailableConfigMethods();
        $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();

        $methodsAvailable = $this->getAvailableRecurrenceMethods(
            $recurrenceProducts,
            $mundipaggPaymentsMethods
        );

        if (!in_array($currentMethod, $methodsAvailable)) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }

    /**
     * @param $recurrenceProducts
     * @param $mundipaggPaymentsMethods
     * @return array
     */
    public function getAvailableRecurrenceMethods(
        $recurrenceProducts,
        $mundipaggPaymentsMethods
    ) {
        if (empty($recurrenceProducts)) {
            return $mundipaggPaymentsMethods;
        }

        $mundipaggPaymentsMethodsFlip = array_flip($mundipaggPaymentsMethods);

        $methodsAvailable = [];
        foreach ($recurrenceProducts as $recurrenceProduct) {

            if (
                $recurrenceProduct->getCreditCard() &&
                in_array('mundipagg_creditcard', $mundipaggPaymentsMethodsFlip)
            ) {
                $methodsAvailable[] = 'mundipagg_creditcard';
            }

            if (
                $recurrenceProduct->getBoleto() &&
                in_array('mundipagg_billet', $mundipaggPaymentsMethodsFlip)
            ) {
                $methodsAvailable[] = 'mundipagg_billet';
            }
        }

        return $methodsAvailable;
    }

    /**
     * @return array
     */
    public function getAvailableConfigMethods()
    {
        $paymentMethods = [];

        if ($this->mundipaggConfig->isBoletoEnabled()) {
            $paymentMethods[] = "mundipagg_billet";
        }

        if ($this->mundipaggConfig->isCreditCardEnabled()) {
            $paymentMethods[] = "mundipagg_creditcard";
        }

        if ($this->mundipaggConfig->isBoletoCreditCardEnabled()) {
            $paymentMethods[] = "mundipagg_billet_creditcard";
        }

        if ($this->mundipaggConfig->isTwoCreditCardsEnabled()) {
            $paymentMethods[] = "mundipagg_two_creditcard";
        }

        if ($this->mundipaggConfig->getVoucherConfig()->isEnabled()) {
            $paymentMethods[] = "mundipagg_voucher";
        }

        if ($this->mundipaggConfig->getDebitConfig()->isEnabled()) {
            $paymentMethods[] = "mundipagg_debit";
        }

        if ($this->mundipaggConfig->getPixConfig()->isEnabled()) {
            $paymentMethods[] = "mundipagg_pix";
        }

        return $paymentMethods;
    }

    /**
     * @param $quote
     * @return RecurrenceEntityInterface[]|null
     */
    public function getRecurrenceProducts($quote)
    {
        $items = $quote->getItems();
        $recurrenceService = new RecurrenceService();

        $recurrenceProducts = [];

        if (!is_array($items)) {
            return $recurrenceProducts;
        }

        foreach ($items as $item) {
            $productId = $item->getProductId();

            $recurrenceProduct =
                $recurrenceService->getRecurrenceProductByProductId($productId);

            if (empty($recurrenceProduct)) {
                continue;
            }

            if ($recurrenceProduct->getRecurrenceType() == Plan::RECURRENCE_TYPE) {
                $recurrenceProducts[] = $recurrenceProduct;
            }

            if (
            !empty($this->recurrenceProductHelper->getSelectedRepetition($item))
            ) {
                $recurrenceProducts[] = $recurrenceProduct;
            }
        }
        return $recurrenceProducts;
    }
}
