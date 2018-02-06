<?php
/**
 * Class AbstractApiCommand
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\Command;

use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Request\BuilderInterface as RequestBuilder;
use Magento\Payment\Gateway\Response\HandlerInterface as ResponseHandler;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Magento\Payment\Gateway\Command\CommandException;
use MundiAPILib\MundiAPIClient;
use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\ConfigInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractApiCommand implements CommandInterface
{
    protected $mundiAPIClient;
    protected $requestBuilder;
    protected $responseHandler;
    protected $validator;
    protected $config;
    protected $logger;

    public function __construct(
        MundiAPIClient $mundiAPIClient,
        RequestBuilder $requestBuilder,
        ResponseHandler $responseHandler,
        ConfigInterface $config,
        ValidatorInterface $validator = null,
        LoggerInterface $logger
    )
    {
        $this->setMundiAPIClient($mundiAPIClient);
        $this->setRequestBuilder($requestBuilder);
        $this->setResponseHandler($responseHandler);
        $this->setConfig($config);
        $this->setValidator($validator);
        $this->logger = $logger;
    }

    abstract protected function sendRequest($request);

    /**
     * {@inheritdoc}
     */
    public function execute(array $commandSubject)
    {
        $request = $this->getRequestBuilder()->build($commandSubject);
        $mundiAPIClient = $this->getMundiAPIClient();

        $response = $this->sendRequest($request);

        if ($this->getValidator()) {
            $result = $this->getValidator()->validate(
                array_merge($commandSubject, ['response' => $response])
            );

            if (! $result->isValid()) {
                $errorMessage = $result->getFailsDescription();
                throw new CommandException(
                    __(reset($errorMessage))
                );
            }
        }

        $this->getResponseHandler()->handle($commandSubject, ['response' => $response]);
        return $this;
    }

    /**
     * @return MundiAPIClient
     */
    protected function getMundiAPIClient()
    {
        return $this->apiClient;
    }

    /**
     * @param MundiAPIClient $mundiAPIClient
     * @return AbstractApiCommand
     */
    protected function setMundiAPIClient(MundiAPIClient $mundiAPIClient)
    {
        $this->apiClient = $mundiAPIClient;
        return $this;
    }



    /**
     * @return ResponseHandler
     */
    protected function getResponseHandler()
    {
        return $this->responseHandler;
    }

    /**
     * @param ResponseHandler $responseHandler
     * @return $this
     */
    protected function setResponseHandler(ResponseHandler $responseHandler)
    {
        $this->responseHandler = $responseHandler;
        return $this;
    }

    /**
     * @return RequestBuilder
     */
    protected function getRequestBuilder()
    {
        return $this->requestBuilder;
    }

    /**
     * @param RequestBuilder $requestBuilder
     * @return $this
     */
    protected function setRequestBuilder(RequestBuilder $requestBuilder)
    {
        $this->requestBuilder = $requestBuilder;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return AbstractApiCommand
     */
    protected function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }



    /**
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param ValidatorInterface|null $validator
     * @return $this
     */
    protected function setValidator(ValidatorInterface $validator = null)
    {
        $this->validator = $validator;
        return $this;
    }
}
