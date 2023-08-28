<?php

namespace Pagarme\Pagarme\Block\Form;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form;
use Magento\Quote\Model\Quote;
use Pagarme\Pagarme\Helper\Adminhtml\CheckoutHelper;

class CreditCard extends Form
{
    /**
     * @var CheckoutHelper
     */
    protected $checkoutHelper;

    public function __construct(
        Context $context,
        CheckoutHelper $checkoutHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutHelper = $checkoutHelper;
    }

    public function getMethodCode()
    {
        return $this->escapeHtml(parent::getMethodCode());
    }

    /**
     * @return string|null
     */
    public function getPublicKey()
    {
        return $this->checkoutHelper->getPublicKey();
    }

    /**
     * @param Quote $quote
     * @return string
     */
    public function getGrandTotal($quote)
    {
        return $this->checkoutHelper->formatGrandTotal($quote->getBaseGrandTotal());
    }

    /**
     * @return string[]
     */
    public function getMonths()
    {
        return $this->checkoutHelper->getMonths();
    }

    /**
     * @return string[]
     */
    public function getYears()
    {
        return $this->checkoutHelper->getYears();
    }

    /**
     * @return array
     */
    public function getAvailableBrands()
    {
        return $this->checkoutHelper->getBrandsAvailables(
            $this->escapeHtml($this->getMethodCode())
        );
    }

    public function getInstallmentsUrl()
    {
        return $this->checkoutHelper->getInstallmentsUrl($this->getBaseUrl());
    }
}
