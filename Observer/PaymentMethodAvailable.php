<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class PaymentMethodAvailable implements ObserverInterface
{
    /**
     * @var PagarmeConfigProvider
     */
    protected $pagarmeConfigProvider;

    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    public function __construct(
        RecurrenceProductHelper $recurrenceProductHelper,
        PagarmeConfigProvider $pagarmeConfigProvider
    ) {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->pagarmeConfigProvider = $pagarmeConfigProvider;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();

        $cannotExecuteObserver = !$quote
            || !$this->pagarmeConfigProvider->isRecurrenceEnabled();
        if ($cannotExecuteObserver) {
            return $this;
        }

        $recurrenceProduct = $this->getRecurrenceProducts($quote);
        if (!empty($recurrenceProduct)) {

            $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            $isPagarmeMethod = strpos($currentMethod, "pagarme");

            $isNotPagarmeMethodOrModuleDisabled =  !$this->pagarmeConfigProvider->getModuleStatus()
                || $isPagarmeMethod === false;
            if ($isNotPagarmeMethodOrModuleDisabled) {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
                return $this;
            }

            $this->switchPaymentMethodsForRecurrence($observer, $recurrenceProduct);
        }

        if (!$this->pagarmeConfigProvider->getModuleStatus()) {
            $this->disablePagarmePaymentMethods($observer);
        }

        return $this;
    }

    /**
     * @param Observer $observer
     */
    private function disablePagarmePaymentMethods(Observer $observer)
    {
        $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();

        $availablePaymentMethod = $this->getAvailableConfigMethods();

        if (in_array($currentMethod, $availablePaymentMethod)) {
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

        $methodsAvailable = [];
        foreach ($recurrenceProducts as $recurrenceProduct) {

            if (
                $recurrenceProduct->getCreditCard() &&
                in_array('pagarme_creditcard', $pagarmePaymentsMethods)
            ) {
                $methodsAvailable[] = 'pagarme_creditcard';
            }

            if (
                $recurrenceProduct->getBoleto() &&
                in_array('pagarme_billet', $pagarmePaymentsMethods)
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
