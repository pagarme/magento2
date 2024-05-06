<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Marketplace;

use Exception;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\Country;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use stdClass;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface as CoreRecipientInterface;

class Recipient extends Template
{
    /**
     * @var Collection
     */
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
     * @var Country
     */
    private $country;


    /**
     * Link constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Country $country
     * @throws Exception
     */
    public function __construct(
        Context                   $context,
        Registry                  $registry,
        CustomerCollectionFactory $customerCollectionFactory,
        Country                   $country,
        RecipientRepository       $recipientRepository
    )
    {
        $this->coreRegistry = $registry;
        $this->customerCollection = $customerCollectionFactory->create();
        $this->recipientRepository = $recipientRepository;
        $this->country = $country;

        Magento2CoreSetup::bootstrap();
        $this->init();
        parent::__construct($context, []);
    }

    protected function init()
    {
        $recipientData = $this->coreRegistry->registry('recipient_data');
        if (!empty($recipientData)) {
            $this->recipient = json_decode($recipientData);
            $this->recipient->recipient->externalId = $this->recipient->externalId;
            $this->recipient->recipient->localId = $this->recipient->localId;
            $this->recipient->recipient->status = $this->recipient->status;
            $this->recipient->recipient->statusUpdated = $this->recipient->statusUpdated;
            $this->recipient->recipient->statusLabel = $this->buildStatusLabel($this->recipient->recipient->status);
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

    /**
     * @param string $countryCode
     * @return array
     */
    public function getAllRegionsOfCountry($countryCode = 'BR')
    {
        return $this->country->loadByCode($countryCode)->getRegions()->getData();
    }

    public function getLabel($key)
    {
        $labels = [
            'no' => 'No',
            'yes' => 'Yes',
            'document_type' => 'Document type',
            'document_number' => 'Document number',
            'name' => 'Name',
            'mother_name' => 'Mother name',
            'email' => 'E-mail',
            'birthdate' => 'Date of birth',
            'monthly_income' => 'Monthly income',
            'profession' => 'Profession',
            'contact_type' => 'Contact type',
            'contact_number' => 'Contact number',
            'mobile_phone' => 'Mobile phone',
            'street' => 'Street',
            'number' => 'Number',
            'complement' => 'Complement',
            'neighborhood' => 'Neighborhood',
            'reference_point' => 'Reference point',
            'state' => 'State/Province',
            'city' => 'City',
            'zip' => 'Zip/Postal Code',
            'select' => 'Select',
        ];

        return __($labels[$key]);
    }

    /**
     * @param string|null $status
     * @return string|null
     */
    private function buildStatusLabel($status)
    {
        if (!is_string($status)) {
            return $status;
        }

        if ($status === CoreRecipientInterface::ACTIVE) {
            $status = 'approved';
        }

        $statusWords = explode('_', $status);
        $statusWords = array_map('ucfirst', $statusWords);
        $statusLabel = implode(" ", $statusWords);
        $statusLabel = trim($statusLabel);
        return __($statusLabel);
    }
}
