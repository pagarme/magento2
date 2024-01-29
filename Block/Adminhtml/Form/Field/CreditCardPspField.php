<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Model\Account;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class CreditCardPspField extends Field
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @param Context $context
     * @param Account $account
     * @param array $data
     */
    public function __construct(
        Context $context,
        Account $account,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->account = $account;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->account->isGateway(PagarmeConfigProvider::CREDIT_CARD_PAYMENT_CONFIG)) {
            return '';
        }

        return parent::render($element);
    }
}
