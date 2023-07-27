<?php

/**
 * Class Invoice
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Customer;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Pagarme\Block\BaseTemplateWithCurrency;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Aggregates\Charge;

class Invoice extends BaseTemplateWithCurrency
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
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param Data $priceHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        Data $priceHelper
    ) {
        parent::__construct($context, $priceHelper, []);
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
        $orderCode = $this->coreRegistry->registry('code');
        $subscriptionId =
            new SubscriptionId(
                $orderCode
            );

        return $this->chargeRepository->findBySubscriptionId($subscriptionId);
    }

    public function getSubscriptionPaymentMethod()
    {
        $codeOrder = $this->coreRegistry->registry('code');

        $pagarmeId = new SubscriptionId($codeOrder);
        $subscription = $this->subscriptionRepository->findByPagarmeId($pagarmeId);
        if (!$subscription) {
            return null;
        }
        return $subscription->getPaymentMethod();
    }

    /**
     * @param int $id
     * @return int
     */
    public function getVisualChargeId($id)
    {
        return $id + 1;
    }

    /**
     * @param Charge $item
     * @return bool
     */
    public function isBillet($item)
    {
        return !empty($item->getBoletoLink());
    }

    /**
     * @param string $codeOrder
     * @throws InvalidParamException|AuthorizationException
     */
    private function validateUserInvoice($codeOrder)
    {
        $subscriptionList = $this->subscriptionRepository->findByCustomerId(
            $this->customerSession->getId()
        );

        /* @var string[] $listSubscriptionCode */
        $listSubscriptionCode = [];
        foreach ($subscriptionList as $subscription) {
            $listSubscriptionCode[] = $subscription->getPagarmeId()->getValue();
        }

        if (!in_array($codeOrder, $listSubscriptionCode)) {
            throw new AuthorizationException(
                __('This order does not belong to this user'),
                null,
                403
            );
        }
    }
}
