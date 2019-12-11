<?php

namespace MundiPagg\MundiPagg\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mundipagg\Core\Recurrence\Aggregates\Plan;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
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
    ){
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
        $currentMethod = $observer->getEvent()->getMethodInstance()->getCode();
        $isMundipaggMethod = strpos($currentMethod, "mundipagg");

        if(!$quote || $isMundipaggMethod === false) {
            return;
        }

        if (!$this->mundipaggConfig->isEnabled()) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
            return;
        }

        $recurrenceProducts = $this->getRecurrenceProducts($quote);

        $mundipaggPaymentsMethods = $this->getAvailableConfigMethods();
        $methodsAvailable = $this->getAvailableRecurrenceMethods(
            $recurrenceProducts,
            $mundipaggPaymentsMethods
        );

        if (!in_array($currentMethod, $methodsAvailable)) {
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }

    public function getAvailableRecurrenceMethods(
        $recurrenceProducts,
        $mundipaggPaymentsMethods
    )
    {
        if (empty($recurrenceProducts)) {
            return $mundipaggPaymentsMethods;
        }

        $flip = array_flip($mundipaggPaymentsMethods);

        unset($flip["mundipagg_billet_creditcard"]);
        unset($flip["mundipagg_two_creditcard"]);

        foreach ($recurrenceProducts as $recurrenceProduct) {

            if (!$recurrenceProduct->getCreditCard()) {
                unset($flip["mundipagg_creditcard"]);
            }

            if (!$recurrenceProduct->getBoleto()) {
                unset($flip["mundipagg_billet"]);
            }
        }

        $mundipaggPaymentsMethods = array_flip($flip);

        return $mundipaggPaymentsMethods;
    }

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

        foreach ($items as $item) {
            $productId = $item->getProductId();

            $recurrenceProduct =
                $recurrenceService->getRecurrenceProductByProductId($productId);

            if (empty($recurrenceProduct)) {
                continue;
            }

            if ($recurrenceProduct->getRecurrenceType() == Plan::RECURRENCE_TYPE) {
                $recurrenceProducts[] =  $recurrenceProduct;
            }

            if (
                !empty($this->recurrenceProductHelper->getRepetitionSelected($item))
            ) {
                $recurrenceProducts[] =  $recurrenceProduct;
            }
        }
        return $recurrenceProducts;
    }
}