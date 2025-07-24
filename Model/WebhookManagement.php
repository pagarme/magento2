<?php

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Phrase;
use Magento\Framework\Webapi\Exception as M2WebApiException;
use Magento\Sales\Model\OrderFactory;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Exceptions\WebhookAlreadyHandledException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Webhook\Services\WebhookReceiverService;
use Pagarme\Core\Webhook\Services\WebhookValidatorService;
use Pagarme\Pagarme\Api\WebhookManagementInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\Account;

class WebhookManagement implements WebhookManagementInterface
{
    const WEBHOOK_SIGNATURE_HEADER = 'X-Hub-Asymmetric-Signature';

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Account
     */
    protected $account;

    /**
     * @var WebhookReceiverService
     */
    protected $webhookReceiverService;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        OrderFactory $orderFactory,
        Account $account,
        WebhookReceiverService $webhookReceiverService,
        RequestInterface $request
    ) {
        $this->orderFactory = $orderFactory;
        $this->account = $account;
        $this->webhookReceiverService = $webhookReceiverService;
        $this->request = $request;
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
        $webhookSignature = $this->request->getHeader(self::WEBHOOK_SIGNATURE_HEADER);
        if (!$webhookSignature) {
            throw new M2WebApiException(
                new Phrase("Webhook signature (" . self::WEBHOOK_SIGNATURE_HEADER . ") not found in header."),
                0,
                M2WebApiException::HTTP_UNAUTHORIZED
            );
        }

        $requestBody = $this->request->getContent();
        if (!$requestBody) {
            throw new M2WebApiException(
                new Phrase("Webhook request body not found."),
                0,
                M2WebApiException::HTTP_UNAUTHORIZED
            );
        }

        // TODO: REMOVE BOTH THIS MOCK DATA AFTER TESTING
        $webhookSignature = "alg=RS256; kid=A7I2qPoXe5vML2XdUu8Bch07bWtAm67bG1LyKceNdgI; signature=IJxzE19Qag9UO0hqNusl_hxqhH-l8K0NfYBl0-AiuKdVSO4m8uh2TenLDf-E10v7NtDMnh6sI8U0ZqCFhPG9rbCFz5oeQ6j-NK5U5p3TjnwqcEoxHohwT3KbguqI0hdMOyzH3FkW8GF5-NXR-vncNrNnqR5E52yDCTIkjc6xEvD6OxDsNMdY-cXik-EKghPTekUZFsbBWGck_as5xlcpMR0s1rf0atiu7Rz0sAmdKOG0T7zspK3HJ7nzxfAm8vENaCLFDAZkLGjHSIa8k-oJ2rBhOJ2Qg_by6-jXQ9r16zXBMV83tnt23fWs2JhsBEL7jD_ADhCakIlKSZMy8dbXTQ";
        $requestBody = '{"data":{"amount":35658,"metadata":{"transaction_id":"242D27D1F43F485BA9372BBE92A2F468","order_code":"1290541","payment_id":"AA0E74EB441448F6BEF35AA168A8D2EB","version":"1.42.0","platform":"Vtex"},"code":"1290541","created_at":"2024-11-28T16:37:08","gateway_id":"492aa3f2-c7ca-48d5-b82e-01badf9fe185","last_transaction":{"amount":35658,"operation_type":"capture","created_at":"2024-11-28T16:37:11","transaction_type":"credit_card","acquirer_nsu":"134715800","acquirer_auth_code":"670912","acquirer_tid":"411958321371328600","acquirer_message":"GetNet|TRANSACAO EXECUTADA COM SUCESSO","gateway_id":"c3563573-107d-4f71-90ab-b7c6f5e12398","gateway_response":{"code":"200"},"acquirer_name":"getnet","updated_at":"2024-11-28T16:37:11","installments":6,"success":true,"acquirer_return_code":"00","id":"tran_Y1wxd8sLMiqkx6OL","acquirer_affiliation_code":"","card":{"holder_document":"*****","exp_month":"*****","created_at":"2024-09-19T12:18:00","billing_address":{"line_1":"sn, Área Rural, Área Rural de Manduri","number":"*****","country":"BR","city":"Manduri","street":"Área Rural","neighborhood":"Área Rural de Manduri","state":"SP","zip_code":"18787899"},"exp_year":"*****","type":"credit","first_six_digits":"479439","updated_at":"2024-11-28T16:37:08","id":"card_LmKDR3ouwCJAR7wd","last_four_digits":"9582","brand":"Visa","holder_name":"andrea vieira","status":"active"},"status":"captured"},"paid_at":"2024-11-28T16:37:11","updated_at":"2024-11-28T16:37:11","paid_amount":35658,"currency":"BRL","id":"ch_LJVEKDaTgzSkNRO2","payment_method":"credit_card","status":"paid","order":{"amount":58018,"metadata":{"transaction_id":"242D27D1F43F485BA9372BBE92A2F468","order_code":"1290541","payment_id":"AA0E74EB441448F6BEF35AA168A8D2EB","version":"1.42.0","platform":"Vtex"},"code":"1290541","closed_at":"2024-11-28T16:37:08","updated_at":"2024-11-28T16:37:11","closed":true,"created_at":"2024-11-28T16:37:08","currency":"BRL","id":"or_kdAKv9DintJ0GOpq","customer_id":"cus_nyO0zJ2cgsABzGJR","status":"paid"},"customer":{"code":"331ae938-7a7d-48e6-85d3-bee9188ea445","address":{"line_1":"sn, Área Rural, Área Rural de Manduri","number":"*****","country":"BR","updated_at":"2024-11-28T16:37:08","city":"Manduri","street":"Área Rural","created_at":"2024-11-28T16:37:08","id":"addr_9XEPKZ6sziMpremv","neighborhood":"Área Rural de Manduri","state":"SP","zip_code":"18787899","status":"active"},"updated_at":"2024-11-28T16:37:08","delinquent":false,"document":"*****","name":"ANDREA VIEIRA","created_at":"2023-07-12T18:45:22","phones":{"mobile_phone":{"country_code":"55","number":"*****","area_code":"14"}},"id":"cus_nyO0zJ2cgsABzGJR","type":"individual","email":"andreakinner@hotmail.com"}},"created_at":"2024-11-28T16:37:11.65Z","id":"hook_9GRgv6LhjuNJgxkv","type":"charge.paid","account":{"name":"Dzarm","id":"acc_k2ngpwlcWLIXAPJ6"}}';
        // END OF 'TODO: REMOVE THIS MOCK DATA AFTER TESTING'

        if (!WebhookValidatorService::validateSignature($requestBody, $webhookSignature)) {
            throw new M2WebApiException(
                new Phrase("Invalid webhook signature."),
                0,
                M2WebApiException::HTTP_UNAUTHORIZED
            );
        }

        try {
            Magento2CoreSetup::bootstrap();

            $postData = new \stdClass();
            $postData->id = $id;
            $postData->type = $type;
            $postData->data = $data;

            if (
                $this->hasMagentoOrder($data) === false
                && $this->isNotRecipientWebhook($type)
            ) {
                $this->logWebhookIdCaseExistsMetadata($data, $id);
                return [
                    "message" => "Webhook Received",
                    "code" => 200
                ];
            }

            if ($type === 'charge.paid') {
                $this->account->saveAccountIdFromWebhook($account);
            }

            return $this->webhookReceiverService->handle($postData);
        } catch (WebhookHandlerNotFoundException | WebhookAlreadyHandledException $e) {
            return [
                "message" => $e->getMessage(),
                "code" => 200
            ];
        } catch (AbstractPagarmeCoreException $e) {
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
        if ($metadata === false || !array_key_exists('platformVersion', $metadata)) {
            return;
        }
        if (strpos($metadata['platformVersion'], "Magento") !== false) {
            $logService = new LogService(
                'Webhook',
                true
            );
            $logService->info(
                "Webhook Received but not proccessed",
                (object)[
                    'webhookId' => $webhookId
                ]
            );
        }
    }
    private function getMetadata($data)
    {
        $metadata = false;
        if (!array_key_exists('order', $data) && !array_key_exists('subscription', $data)) {
            return false;
        }
        if (array_key_exists('metadata', $data)) {
            $metadata = $data['metadata'];
        }
        return $metadata;
    }

    private function hasMagentoOrder($data)
    {
        $code = 0;
        if (array_key_exists('subscription', $data)) {
            $code = $data['subscription']['code'];
        }
        if (array_key_exists('order', $data)) {
            $code = $data['order']['code'];
        }
        $order = $this->orderFactory->create()->loadByIncrementId($code);
        return $order->getId() ?? false;
    }

    private function isNotRecipientWebhook($type)
    {
        return strpos($type, 'recipient') === false;
    }
}
