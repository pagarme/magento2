<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Api\BulkApiInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Factories\BulkSingleResponseFactory;

class Bulk implements BulkApiInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Curl
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
        Curl $curl,
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
        $bodyParams = $this->request->getBodyParams();
        $this->validateRequestsParam($bodyParams);

        if (isset($bodyParams['access_token'])) {
            $accessToken = $bodyParams['access_token'];
            $this->curl->addHeader("Authorization", "Bearer {$accessToken}");
        }

        $this->curl->addHeader("Content-Type", "application/json");

        foreach ($bodyParams['requests'] as $key => $request) {
            $validationInfo = $this->validateSingleRequestParams($key, $request);

            if ($validationInfo['code'] === self::HTTP_OK) {
                $responseFactory = new BulkSingleResponseFactory();
                $curlResponse = $this->executeCurl($key, $request);
                $response = $responseFactory->createFromCurlResponse($curlResponse);
                $this->setFormatedResponse($key, $response, $request);
            }

            if ($validationInfo['code'] === self::HTTP_BAD_REQUEST) {
                $responseFactory = new BulkSingleResponseFactory();
                $response = $responseFactory->createFromArrayData($validationInfo);
                $this->setFormatedResponse($key, $response, $request);
            }
        }

        return $this->getFormatedResponse();
    }

    private function validateRequestsParam($bodyParams)
    {
        if (!isset($bodyParams['requests'])) {
            throw new MagentoException(
                __("Requests parameter is required."),
                0,
                self::HTTP_BAD_REQUEST
            );
        }

        if (empty($bodyParams['requests'])) {
            throw new MagentoException(
                __("Requests parameter can not be empty."),
                0,
                self::HTTP_BAD_REQUEST
            );
        }

        if (!is_array($bodyParams['requests'])) {
            throw new MagentoException(
                __("Requests parameter must be an array."),
                0,
                self::HTTP_BAD_REQUEST
            );
        }
    }

    private function validateSingleRequestParams(
        int $key,
        array $request
    ): array {
        if (!isset($request['method'])) {
            return [
                "message" => "Method parameter in array requests is required.",
                "code" => self::HTTP_BAD_REQUEST
            ];
        }

        if (!isset($request['path'])) {
            return [
                "message" => "Path parameter in array requests is required.",
                "code" => self::HTTP_BAD_REQUEST
            ];
        }

        if (!isset($request['params'])) {
            return [
                "message" => "Params parameter in array requests is required.",
                "code" => self::HTTP_BAD_REQUEST
            ];
        }

        return [
            "message" => "Successfully validated.",
            "code" => self::HTTP_OK
        ];
    }

    private function executeCurl(int $key, array $request)
    {
        $method = $request['method'];
        $apiUrl = $this->getApiBaseUrl() . $request['path'];
        $params = json_encode($request['params']);

        try {
            $this->curl->$method($apiUrl, $params);
            return $this->curl;
        } catch (\Exception $exception) {
            throw new MagentoException(
                __($exception->getMessage()),
                0,
                self::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function getApiBaseUrl(): string
    {
        $defaultStoreViewCode = Magento2CoreSetup::getDefaultStoreViewCode();
        return $this->baseUrl . "rest/{$defaultStoreViewCode}/V1/pagarme";
    }

    private function setFormatedResponse(
        int $index,
        BulkSingleResponse $response,
        array $request
    ): void {
        $this->responseArray[] = array(
            "index" => $index,
            "status" => $response->getStatus(),
            "body" => $response->getBody(),
            "path" => $request['path'] ?? null,
            "method" => $request['method'] ?? null,
        );
    }

    private function getFormatedResponse(): array
    {
        return $this->responseArray;
    }
}