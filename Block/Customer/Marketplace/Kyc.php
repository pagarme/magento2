<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */
declare(strict_types=1);

namespace Pagarme\Pagarme\Block\Customer\Marketplace;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Pagarme\Model\ResourceModel\Recipients\CollectionFactory;

class Kyc extends Template
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context  $context,
        Session           $customerSession,
        CollectionFactory $collectionFactory,
        array             $data = [])
    {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    public function getRecipient()
    {
        return $this->collectionFactory->create()->addFieldToFilter(
            'external_id',
            ['eq' => $this->getCustomerId()]
        )->getFirstItem();
    }

    /**
     * Get customerid
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
}
