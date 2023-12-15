<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Pagarme\Model\Account;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class InstallmentsNumber extends Field
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @param Context $context
     * @param CollectionFactory $configCollectionFactory
     * @param Account $account
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $configCollectionFactory,
        Account $account,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configCollectionFactory = $configCollectionFactory;
        $this->account = $account;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $isGateway = $this->account->isGateway(PagarmeConfigProvider::CREDIT_CARD_PAYMENT_CONFIG);
        if ($isGateway) {
            $classes = $element->getClass();
            $classes = str_replace('number-range-1-12', '', $classes);
            $classes .= ' number-range-1-24';
            $element->setClass($classes);

            $comment = $element->getComment();
            $comment = str_replace('12', '24', $comment);
            $element->setComment($comment);
        }
        return parent::render($element);
    }
}
