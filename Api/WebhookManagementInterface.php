<?php

namespace MundiPagg\MundiPagg\Api;

interface WebhookManagementInterface
{
    /**
     * @api
     * @param mixed $data
     * @return boolean
     */
    public function save($data);
}
