<?php

namespace Pagarme\Pagarme\Factories;

use Magento\Framework\HTTP\Client\Curl;
use Pagarme\Pagarme\Model\Api\BulkSingleResponse;

class BulkSingleResponseFactory
{
    public function createFromCurlResponse(Curl $curlResponse): BulkSingleResponse
    {
        $body = json_decode($curlResponse->getBody(), true);

        if (!is_array($body)) {
            $body = ['message' => $body];
        }

        $bulkSingleResponse = new BulkSingleResponse;
        $bulkSingleResponse->setStatus($curlResponse->getStatus());
        $bulkSingleResponse->setBody($body);
        return $bulkSingleResponse;
    }

    public function createFromArrayData(array $arrayData): BulkSingleResponse
    {
        $bulkSingleResponse = new BulkSingleResponse;
        $bulkSingleResponse->setStatus($arrayData['code']);
        $bulkSingleResponse->setBody(['message' => $arrayData['message']]);
        return $bulkSingleResponse;
    }
}