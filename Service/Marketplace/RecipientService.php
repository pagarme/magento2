<?php

namespace Pagarme\Pagarme\Service\Marketplace;

use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Middle\Proxy\RecipientProxy;
use Pagarme\Pagarme\Model\CoreAuth;

class RecipientService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    public function createRecipient($recipient)
    {
        $recipientProxy = new RecipientProxy($this->coreAuth);
        return $recipientProxy->create($recipient);
    }

    public function searchRecipient($recipientId)
    {
        $recipientProxy = new RecipientProxy($this->coreAuth);
        $recipient =  $recipientProxy->getFromPagarme($recipientId);
        $kycStatus = $recipient->kyc_details->status ?? '';
        $recipient->status = Recipient::parseStatus($recipient->status, $kycStatus);
        return $recipient;
    }

    public function createKycLink($recipientId)
    {
        $recipientProxy = new RecipientProxy($this->coreAuth);
        return $recipientProxy->createKycLink($recipientId);
    }
}
