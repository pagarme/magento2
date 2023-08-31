<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Interfaces\RecurrenceEntityInterface;
use Pagarme\Core\Recurrence\Services\PlanService;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductPlanHelper;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class UpdateProductPlanObserver implements ObserverInterface
{
    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    /**
     * @var PagarmeConfigProvider
     */
    protected $pagarmeConfigProvider;

    public function __construct(
        RecurrenceProductHelper $recurrenceProductHelper,
        PagarmeConfigProvider $pagarmeConfigProvider
    ) {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
        $this->pagarmeConfigProvider = $pagarmeConfigProvider;
    }

    public function execute(Observer $observer)
    {
       $event = $observer->getEvent();
       $product = $event->getProduct();

       $cannotExecuteObserver = !$product
           || !$this->pagarmeConfigProvider->isRecurrenceEnabled();
       if ($cannotExecuteObserver) {
           return $this;
       }

       $productId = $product->getEntityId();
       $recurrenceService = new RecurrenceService();
       $recurrence = $recurrenceService->getRecurrenceProductByProductId($productId);

       if (!$recurrence || $recurrence->getRecurrenceType() !== Plan::RECURRENCE_TYPE) {
           return $this;
       }

       return $this->updatePlan($recurrence);
    }

    protected function updatePlan(RecurrenceEntityInterface $recurrence)
    {
        try {
            ProductPlanHelper::mapperProductPlan($recurrence);
            $service = new PlanService();
            $service->updatePlanAtPagarme($recurrence);
            $service->save($recurrence);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $this;
    }
}
