<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Pagarme\Pagarme\Model\Account;

class CustomPaymentFieldset extends Fieldset
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @var string
     */
    protected $paymentMethodName;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Account $account
     * @param string $paymentMethodName
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Account $account,
        string $paymentMethodName = '',
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->account = $account;
        $this->paymentMethodName = $paymentMethodName;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (empty($this->paymentMethodName) || $this->account->isPSP($this->paymentMethodName)) {
            return '';
        }

        return parent::render($element);
    }
}
