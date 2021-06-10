<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Api\BulkApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Bulk implements BulkApiInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $responseArray;

    public function __construct(
        Request $request,
        \Magento\Framework\HTTP\Client\Curl $curl,
        StoreManagerInterface $storeManager
    )
    {
        $this->request = $request;
        $this->curl = $curl;
        $this->baseUrl = $storeManager->getStore()->getBaseUrl();
    }

    /**
     * @return array|mixed
     */
    public function execute()
    {
        $requestParams = $this->request->getBodyParams();
        $accessToken = $requestParams['access_token'];
        $this->curl->addHeader("Authorization", "Bearer {$accessToken}");

        foreach ($requestParams['requests'] as $key => $request) {
            $this->executeCurl($key, $request);
        }

        return $this->getFormatedResponse();
    }

    private function executeCurl(int $key, array $request): void
    {
        $method = $request['method'];
        $apiUrl = $this->getChargeUrl() . $request['path'];
        $params = $request['params'];

        try {
            $this->curl->$method($apiUrl, $params);
            $curlResponseRequest = $this->curl;
        }catch (\Exception $exception) {
            throw new MagentoException(__($exception->getMessage()), 0, 500);
        }

        $this->setFormatedResponse(
            $key,
            $curlResponseRequest,
            $request
        );
    }

    private function getChargeUrl(): string
    {
        $defaultStoreViewCode = Magento2CoreSetup::getDefaultStoreViewCode();
        return $this->baseUrl . "rest/{$defaultStoreViewCode}/V1/pagarme";
    }

    private function setFormatedResponse(
        int $index,
        Curl $response,
        array $request
    ): void {
        $this->responseArray[] = array(
            "index" => $index,
            "status" => $response->getStatus(),
            "body" => json_decode($response->getBody(), true),
            "path" => $request['path'],
            "method" => $request['method'],
        );
    }

    private function getFormatedResponse(): array
    {
        return $this->responseArray;
    }
}