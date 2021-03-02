<?php

namespace Pagarme\Pagarme\Api;

interface WebhookManagementInterface
{
    /**
     * @api
     * @param mixed $id
     * @param mixed $type
     * @param mixed $data
     * @return boolean
     */
    public function save($id, $type, $data);
}
