<?php

namespace Pagarme\Pagarme\Api;

interface RecipientInterface
{
    /**
     *
     * @param mixed $data
     * @return mixed
     */
    public function saveFormData();

    /**
     *
     * @param mixed $data
     * @return string
     */
    public function searchRecipient(): string;
}
