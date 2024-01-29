<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Model\Account;

class GatewayField extends Field
{
    /**
     * @var string
     */
    protected $paymentMethodName;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @param Context $context
     * @param Account $account
     * @param string $paymentMethodName
     * @param array $data
     */
    public function __construct(
        Context $context,
        Account $account,
        string $paymentMethodName = '',
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->account = $account;
        $this->paymentMethodName = $paymentMethodName;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (empty($this->paymentMethodName) || !$this->account->isGateway($this->paymentMethodName)) {
            return '';
        }

        return parent::render($element);
    }

}
