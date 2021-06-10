<?php

namespace Pagarme\Pagarme\Model\Api;

final class ResponseMessage
{
    /** @var string */
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}