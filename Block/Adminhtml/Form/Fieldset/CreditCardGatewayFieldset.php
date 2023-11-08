<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Fieldset;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\Js;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Pagarme\Pagarme\Model\Account;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class CreditCardGatewayFieldset extends Fieldset
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @param Context $context
     * @param Session $authSession
     * @param Js $jsHelper
     * @param Account $account
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Account $account,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data, $secureRenderer);
        $this->account = $account;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->account->isGateway(PagarmeConfigProvider::CREDIT_CARD_PAYMENT_CONFIG)) {
            return '';
        }

        return parent::render($element);
    }
}
