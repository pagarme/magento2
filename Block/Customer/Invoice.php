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
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Customer\Model\Session;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Pagarme\Api\Data\RecurrenceProductsSubscriptionInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Pagarme\Helper\NumberFormatHelper;

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
     * @var NumberFormatHelper
     */
    private $numberFormatter;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param NumberFormatHelper $numberFormatter
     * @throws AuthorizationException
     * @throws InvalidParamException
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        NumberFormatHelper $numberFormatter
    ) {
        parent::__construct($context, []);
        Magento2CoreSetup::bootstrap();

        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->numberFormatter = $numberFormatter;
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
     * @return string
     */
    public function getBilletHeader()
    {
        if (!$this->isBillet()) {
            return "";
        }

        return sprintf('<th>%s</th>', __('Billet'));
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getInvoicesTableBody()
    {
        $tbody = "";

        foreach ($this->getAllChargesByCodeOrder() as $id => $item) {
            $tbody .= "<tr>";
            $visualId = $id + 1;
            $tbody .= $this->formatTableDataCell($visualId);
            $tbody .= $this->formatNumberTableDataCell($item->getAmount());
            $tbody .= $this->formatNumberTableDataCell($item->getPaidAmount());
            $tbody .= $this->formatNumberTableDataCell($item->getCanceledAmount());
            $tbody .= $this->formatNumberTableDataCell($item->getRefundedAmount());
            $tbody .= $this->formatTableDataCell($item->getStatus()->getStatus());
            $tbody .= $this->formatTableDataCell($item->getPaymentMethod()->getPaymentMethod());
            $tbody .= $this->addBilletButton($item);
            $tbody .= '</tr>';
        }

        return $tbody;
    }

    /**
     * @param mixed $item
     * @return string
     */
    private function addBilletButton($item)
    {
        $button = '';
        if (!$this->isBillet()) {
            return $button;
        }

        $button = '<td>';
        $hasBilletLink = !empty($item->getBoletoLink());
        if ($hasBilletLink) {
            $button .= sprintf(
                '<button onclick="location.href = \'%s\';" id="details">%s</button>',
                $item->getBoletoLink(),
                'download'
            );
        }
        $button .= '</td>';

        return $button;
    }

    /**
     * @param mixed $text
     * @return string
     */
    private function formatTableDataCell($text)
    {
        return sprintf('<td>%s</td>', $text);
    }

    /**
     * @param mixed $number
     * @return string
     */
    private function formatNumberTableDataCell($number)
    {
        return $this->formatTableDataCell($this->formatNumber($number));
    }

    /**
     * @param mixed $number
     * @return false|string
     */
    private function formatNumber($number)
    {
        return $this->numberFormatter->formatToLocalCurrency(($number) / 100);
    }

    /**
     * @return bool
     */
    private function isBillet()
    {
        return $this->getSubscriptionPaymentMethod() === RecurrenceProductsSubscriptionInterface::BOLETO;
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
