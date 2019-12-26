<?php

/**
 * Class Invoice
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2019 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Mundipagg\Core\Recurrence\Repositories\SubscriptionRepository;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Recurrence\Repositories\ChargeRepository;
use Mundipagg\Core\Recurrence\Aggregates\Charge;

class Invoice extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ChargeRepository
     */
    protected $chargeRepository;

    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Link constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @throws InvalidParamException
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry
    ) {
        parent::__construct($context, []);
        Magento2CoreSetup::bootstrap();

        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->chargeRepository = new ChargeRepository();
        $this->subscriptionRepository = new SubscriptionRepository();

        $this->validateUserInvoice($this->coreRegistry->registry('code'));
    }

    /**
     * @return AbstractEntity|Charge[]
     * @throws InvalidParamException
     */
    public function getAllChargesByCodeOrder()
    {
        $codeOrder = $this->coreRegistry->registry('code');
        return $this->chargeRepository->findBySubscriptionId($codeOrder);
    }

    public function getSubscriptionPaymentMethod()
    {
        $codeOrder = $this->coreRegistry->registry('code');

        $mundipaggId = new SubscriptionId($codeOrder);
        $subscription = $this->subscriptionRepository->findByMundipaggId($mundipaggId);
        if (!$subscription) {
            return null;
        }
        return $subscription->getPaymentMethod();
    }

    /**
     * @param string $codeOrder
     * @throws InvalidParamException
     */
    private function validateUserInvoice($codeOrder)
    {
        $subscriptionList = $this->subscriptionRepository->findByCustomerId(
            $this->customerSession->getId()
        );

        /* @var string[] $listSubscriptionCode */
        $listSubscriptionCode = [];
        foreach ($subscriptionList as $subscription) {
            $listSubscriptionCode[] = $subscription->getMundipaggId()->getValue();
        }

        if (!in_array($codeOrder, $listSubscriptionCode)) {
            throw new \Exception(
                'Esse pedido não pertence a esse usuário',
                403
            );
        }
    }
}
