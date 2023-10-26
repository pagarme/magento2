<?php

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as M2WebApiException;
use Magento\Sales\Model\OrderFactory;
use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Exceptions\WebhookAlreadyHandledException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Webhook\Services\WebhookReceiverService;
use Pagarme\Pagarme\Api\WebhookManagementInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\Account;

class WebhookManagement implements WebhookManagementInterface
{

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Account
     */
    protected $account;

    public function __construct(
        OrderFactory $orderFactory,
        Account $account
    ) {
        $this->orderFactory = $orderFactory;
        $this->account = $account;
    }
    /**
     * @api
     * @param mixed $id
     * @param mixed $account
     * @param mixed $type
     * @param mixed $data
     * @return array|bool
     */
    public function save($id, $type, $data, $account)
    {
        try {
            Magento2CoreSetup::bootstrap();

            $postData = new \stdClass();
            $postData->id = $id;
            $postData->type = $type;
            $postData->data = $data;

            if($this->hasMagentoOrder($data) === false) {
                $this->logWebhookIdCaseExistsMetadata($data, $id);
                return [
                    "message" => "Webhook Received",
                    "code" => 200
                ];
            }

            if ($type === 'charge.paid') {
                $this->account->saveAccountIdFromWebhook($account);
            }

            $webhookReceiverService = new WebhookReceiverService();
            return $webhookReceiverService->handle($postData);
        } catch (WebhookHandlerNotFoundException | WebhookAlreadyHandledException $e) {
            return [
                "message" => $e->getMessage(),
                "code" => 200
            ];
        } catch(AbstractPagarmeCoreException $e) {
            throw new M2WebApiException(
                new Phrase($e->getMessage()),
                0,
                M2WebApiException::HTTP_BAD_REQUEST
            );
        }
    }
    private function logWebhookIdCaseExistsMetadata($webhookData, $webhookId)
    {
        $metadata = $this->getMetadata($webhookData);
        if($metadata === false || !array_key_exists('platformVersion', $metadata)) {
            return;
        }
        if(strpos($metadata['platformVersion'], "Magento") !== false) {
            $logService = new LogService(
                'Webhook',
                true
            );
            $logService->info(
                "Webhook Received but not proccessed",
                (object)['webhookId' => $webhookId
            ]);
        }
    }
    private function getMetadata($data)
    {
        $metadata = false;
        if(!array_key_exists('order', $data) && !array_key_exists('subscription', $data)) {
            return false;
        }
        if(array_key_exists('metadata', $data)) {
            $metadata = $data['metadata'];
        }
        return $metadata;
    }
    private function hasMagentoOrder($data)
    {
        $code = 0;
        if(array_key_exists('subscription', $data)) {
            $code = $data['subscription']['code'];
        }
        if(array_key_exists('order', $data)) {
            $code = $data['order']['code'];
        }
        $order = $this->orderFactory->create()->loadByIncrementId($code);
        return $order->getId() ?? false;
    }
}
