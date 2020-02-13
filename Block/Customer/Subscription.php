<?php

namespace MundiPagg\MundiPagg\Block\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;
use MundiPagg\MundiPagg\Ui\Component\Recurrence\Column\TotalCyclesByProduct;

class Subscription extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    protected $objectManager;

    /**
     * Link constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        parent::__construct($context, []);
        Magento2CoreSetup::bootstrap();

        $this->customerSession = $customerSession;
        $this->subscriptionRepository = new SubscriptionRepository();
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @return AbstractEntity|\Mundipagg\Core\Recurrence\Aggregates\Subscription[]|null
     * @throws InvalidParamException
     */
    public function getAllSubscriptionRecurrenceCoreByCustomerId()
    {
        return $this->subscriptionRepository->findByCustomerId(
            $this->customerSession->getId()
        );
    }

    public function getHighestProductCycle($item)
    {
        $recurrenceProductHelper = new RecurrenceProductHelper();
        $magentoOrder =
            $this->objectManager
                ->get('Magento\Sales\Model\Order')
                ->loadByIncrementId($item->getCode());
        $products = $magentoOrder->getAllItems();

        $cycles = [];

        foreach ($products as $product) {
            $cycles[] =
                $recurrenceProductHelper
                    ->getSelectedRepetitionByProduct($product);
        }

        return $recurrenceProductHelper->returnHighestCycle($cycles);
    }
}
