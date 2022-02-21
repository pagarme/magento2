<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Marketplace;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;
use stdClass;

class Recipient extends Template
{

    private $customerCollection;

    /**
     * @var RecipientRepository
     */
    private $recipientRepository;

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
        $this->recipientRepository = new RecipientRepository();

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
            $sellerId = $seller->getSellerId();

            $recipient = $this->recipientRepository->findBySellerId($sellerId);

            if (!empty($recipient)) {
                continue;
            }

            $entityIds[] = $sellerId;
        }

        $customers = $this->customerCollection
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', array('in' => $entityIds))
            ->getData();

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
