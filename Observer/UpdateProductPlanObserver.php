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

class UpdateProductPlanObserver implements ObserverInterface
{
    /**
     * @var RecurrenceProductHelper
     */
    protected $recurrenceProductHelper;

    public function __construct(RecurrenceProductHelper $recurrenceProductHelper)
    {
        Magento2CoreSetup::bootstrap();
        $this->recurrenceProductHelper = $recurrenceProductHelper;
    }

    public function execute(Observer $observer)
    {
       $event = $observer->getEvent();
       $product = $event->getProduct();

       if (!$product) {
           return $this;
       }

       $productId = $product->getEntityId();
       $recurrenceService = new RecurrenceService();
       $recurrence = $recurrenceService->getRecurrenceProductByProductId($productId);

       if (!$recurrence || $recurrence->getRecurrenceType() !== Plan::RECURRENCE_TYPE) {
           return $this;
       }

       return $this->updatePlan($recurrence, $product);
    }

    protected function updatePlan(RecurrenceEntityInterface $recurrence)
    {
        try{
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
