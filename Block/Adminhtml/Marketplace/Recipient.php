<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Marketplace;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use stdClass;

class Recipient extends Template
{

    private $customerCollection;
    /**
     * @var Registry
     */
    private $coreRegistry;


    /**
     * @var array
     */
    private $sellers = [];

    /**
     * @var stdClass
     */
    private $recipient = null;


    /**
     * Link constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->coreRegistry = $registry;
        $this->customerCollection = $customerCollectionFactory->create();

        Magento2CoreSetup::bootstrap();
        parent::__construct($context, []);

        $recipientData = $this->coreRegistry->registry('recipient_data');
        if (!empty($recipientData)) {
            $this->recipient = json_decode($recipientData);
            $this->recipient->recipient->externalId = $this->recipient->externalId;
            $this->recipient->recipient->localId = $this->recipient->localId;
            $this->recipient = $this->recipient->recipient;
        }

        $sellerData = $this->coreRegistry->registry('sellers');
        if (!empty($sellerData)) {
            $this->sellers = $this->buildSellerData($sellerData);
        }
    }

    private function buildSellerData($serializedSellerData)
    {
        $sellerData = unserialize($serializedSellerData);
        $entityIds = [];

        foreach ($sellerData as $seller) {
            $entityIds[$seller->getEntityId()] = $seller->getSellerId();
        }

        $customers = $this->customerCollection
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => array_keys($entityIds)))
            ->getData();

        foreach ($customers as &$customer) {
            $customer['seller_id'] = $entityIds[$customer['entity_id']];
        }

        return $customers;
    }

    public function getEditRecipient()
    {
        if (empty($this->recipient)) {
            return "";
        }

        return json_encode($this->recipient);
    }

    public function getRecipientId()
    {
        if (is_null($this->recipient)) {
            return '';
        }

        return $this->recipient->id;
    }

    public function getLocalId()
    {
        if (is_null($this->recipient)) {
            return '';
        }

        return $this->recipient->localId;
    }

    public function getSellers()
    {
        if (is_null($this->sellers)) {
            return [];
        }

        return $this->sellers;
    }
}
