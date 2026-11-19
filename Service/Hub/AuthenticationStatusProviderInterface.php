<?php

namespace Pagarme\Pagarme\Service\Hub;

interface AuthenticationStatusProviderInterface
{
    /**
     * Final status (trans_status) the Hub holds for a 3DS authentication.
     *
     * @param string $transactionId tds_server_trans_id
     * @return string
     * @throws AuthenticationNotFoundException when the Hub does not know the transaction
     * @throws HubUnavailableException when the Hub could not give a trustworthy answer
     */
    public function getStatus($transactionId);
}
