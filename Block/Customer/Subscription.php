<?php

namespace Pagarme\Pagarme\Block\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\RecurrenceProductHelper;

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
     * @var RecurrenceProductHelper
     */
    private $recurrenceProductHelper;

    /**
     * Link constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @throws \Exception
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
        $this->recurrenceProductHelper = new RecurrenceProductHelper();
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

    public function getHighestProductCycle($subscription)
    {
        return $this->recurrenceProductHelper
            ->getHighestProductCycle(
                $subscription->getCode(),
                $subscription->getPlanIdValue()
            );

    }

    public function getInterval($subscription)
    {
        $repetition = new Repetition();
        $repetitionService = new RepetitionService();

        $repetition->setInterval($subscription->getIntervalType());
        $repetition->setIntervalCount($subscription->getIntervalCount());

        return $repetitionService->getCycleTitle($repetition);
    }

    public function getSubscriptionCreatedDate($subscription)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $subscription = $subscriptionRepository->findByMundipaggId($subscription->getSubscriptionId());
        $createdAt = new \Datetime($subscription->getCreatedAt());
        return $createdAt->format('d/m/Y');

    }
}
