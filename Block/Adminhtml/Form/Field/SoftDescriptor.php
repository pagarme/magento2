<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Model\Account;

class SoftDescriptor extends Field
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
        if (empty($this->paymentMethodName)) {
            return '';
        }
        $isGateway = $this->account->isGateway($this->paymentMethodName);
        if ($isGateway) {
            $classes = $element->getClass();
            $classes = str_replace('maximum-length-13', '', $classes);
            $classes .= ' maximum-length-22';
            $element->setClass($classes);

            $comment = $element->getComment();
            $comment = str_replace('13', '22', $comment);
            $element->setComment($comment);
        }
        return parent::render($element);
    }
}
