<?php

namespace Pagarme\Pagarme\Service\Marketplace;

use Magento\Framework\Exception\NoSuchEntityException;
use Pagarme\Pagarme\Model\RecipientFactory as ModelFactoryRecipient;
use Pagarme\Pagarme\Model\ResourceModel\Recipients as ResourceModelRecipient;

class KycLinkService
{
    /**
     * @var ModelFactoryRecipient
     */
    private $modelFactoryRecipient;

    /**
     * @var ResourceModelRecipient
     */
    private $resourceModelRecipient;

    /**
     * @var RecipientService
     */
    private $recipientService;

    public function __construct(
        ModelFactoryRecipient $modelFactoryRecipient,
        ResourceModelRecipient $resourceModelRecipient,
        RecipientService $recipientService
    ) {
        $this->modelFactoryRecipient = $modelFactoryRecipient;
        $this->resourceModelRecipient = $resourceModelRecipient;
        $this->recipientService = $recipientService;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function createKycLink(string $recipientId)
    {
        return $this->generateKycLink($this->loadRecipient($recipientId));
    }

    /**
     * Ownership is enforced here so that a recipient is never exposed to a customer who doesn't own it.
     *
     * @throws NoSuchEntityException
     */
    public function createKycLinkForCustomer(string $recipientId, int $customerId)
    {
        $recipientModel = $this->loadRecipient($recipientId);
        if ((string) $recipientModel->getExternalId() !== (string) $customerId) {
            throw new NoSuchEntityException(__('Recipient not founded.'));
        }
        return $this->generateKycLink($recipientModel);
    }

    /**
     * @throws NoSuchEntityException
     */
    private function loadRecipient(string $recipientId)
    {
        $recipientModel = $this->modelFactoryRecipient->create();
        $this->resourceModelRecipient->load($recipientModel, $recipientId);
        if (empty($recipientModel->getPagarmeId())) {
            throw new NoSuchEntityException(__('Recipient not founded.'));
        }
        return $recipientModel;
    }

    /**
     * @throws NoSuchEntityException
     */
    private function generateKycLink($recipientModel)
    {
        $kycLink = $this->recipientService->createKycLink($recipientModel->getPagarmeId());
        if (empty($kycLink->url) || empty($kycLink->base64_qrcode)) {
            throw new NoSuchEntityException(__('Failed to generate the security validation link.'));
        }
        return $kycLink;
    }
}
