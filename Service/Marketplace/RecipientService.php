<?php

namespace Pagarme\Pagarme\Service\Marketplace;

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
        $this->coreAuth = new CoreAuth('');
    }

    public function createRecipient($recipient)
    {
        $recipientProxy = new RecipientProxy($this->coreAuth);
        $recipientProxy->create($recipient);
        $abc = $recipientProxy;
        return $abc;
    }
}
