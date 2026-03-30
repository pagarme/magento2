<?php

namespace Pagarme\Pagarme\Api;

interface WebhookManagementInterface
{
    /**
     * @api
     * @param mixed $id
     * @param mixed $type
     * @param mixed $data
     * @param mixed $account
     * @param mixed $identifier
     * @return array
     */
    public function save($id, $type, $data, $account, $identifier = null);
}
