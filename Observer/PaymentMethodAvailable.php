<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var \Pagarme\Core\Kernel\Aggregates\Configuration
     */
    protected $pagarmeConfig;
    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    public function __construct(
        RecurrenceProductHelper $recurrenceProductHelper
    ) {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->pagarmeConfig = Magento2CoreSetup::getModuleConfiguration();
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
            $isPagarmeMethod = strpos($currentMethod, "pagarme");

            if (
                !$this->pagarmeConfig->isEnabled() ||
                $isPagarmeMethod === false
            ) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
                return;
            }

            $this->switchPaymentMethodsForRecurrence($observer, $recurrenceProduct);
        }

        if (!$this->pagarmeConfig->isEnabled()) {
            $this->disablePagarmePaymentMethods($observer);
        }

        return;
    }

    /**
     * @param Observer $observer
     */
    private function disablePagarmePaymentMethods(Observer $observer)
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
        $pagarmePaymentsMethods = $this->getAvailableConfigMethods();
        $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();

        $methodsAvailable = $this->getAvailableRecurrenceMethods(
            $recurrenceProducts,
            $pagarmePaymentsMethods
        );

        if (!in_array($currentMethod, $methodsAvailable)) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }

    /**
     * @param $recurrenceProducts
     * @param $pagarmePaymentsMethods
     * @return array
     */
    public function getAvailableRecurrenceMethods(
        $recurrenceProducts,
        $pagarmePaymentsMethods
    ) {
        if (empty($recurrenceProducts)) {
            return $pagarmePaymentsMethods;
        }

        $pagarmePaymentsMethodsFlip = array_flip($pagarmePaymentsMethods);

        $methodsAvailable = [];
        foreach ($recurrenceProducts as $recurrenceProduct) {

            if (
                $recurrenceProduct->getCreditCard() &&
                in_array('pagarme_creditcard', $pagarmePaymentsMethodsFlip)
            ) {
                $methodsAvailable[] = 'pagarme_creditcard';
            }

            if (
                $recurrenceProduct->getBoleto() &&
                in_array('pagarme_billet', $pagarmePaymentsMethodsFlip)
            ) {
                $methodsAvailable[] = 'pagarme_billet';
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

        if ($this->pagarmeConfig->isBoletoEnabled()) {
            $paymentMethods[] = "pagarme_billet";
        }

        if ($this->pagarmeConfig->isCreditCardEnabled()) {
            $paymentMethods[] = "pagarme_creditcard";
        }

        if ($this->pagarmeConfig->isBoletoCreditCardEnabled()) {
            $paymentMethods[] = "pagarme_billet_creditcard";
        }

        if ($this->pagarmeConfig->isTwoCreditCardsEnabled()) {
            $paymentMethods[] = "pagarme_two_creditcard";
        }

        if ($this->pagarmeConfig->getVoucherConfig()->isEnabled()) {
            $paymentMethods[] = "pagarme_voucher";
        }

        if ($this->pagarmeConfig->getDebitConfig()->isEnabled()) {
            $paymentMethods[] = "pagarme_debit";
        }

        if ($this->pagarmeConfig->getPixConfig()->isEnabled()) {
            $paymentMethods[] = "pagarme_pix";
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
