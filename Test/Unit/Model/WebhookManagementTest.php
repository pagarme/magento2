<?php

namespace Pagarme\Pagarme\Test\Unit\Model;

use Mockery;
use Magento\Sales\Model\Order;
use Pagarme\Pagarme\Model\Account;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Model\WebhookManagement;
use Pagarme\Core\Webhook\Services\WebhookReceiverService;
use Pagarme\Core\Webhook\Services\WebhookValidatorService;

class WebhookManagementTest extends BaseTest
{
    public function testSaveWithRecipientWebhook()
    {
        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();


        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('loadByIncrementId')
            ->andReturnSelf();
        $orderMock->shouldReceive('getId')
            ->andReturnFalse();

        $orderFactoryMock = Mockery::mock(OrderFactory::class);
        $orderFactoryMock->shouldReceive('create')
            ->andReturn($orderMock);
        $accountMock = Mockery::mock(Account::class);

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getHeader')
            ->with(WebhookManagement::WEBHOOK_SIGNATURE_HEADER)
            ->andReturn('alg=RS256; kid=test-kid; signature=test-sig');
        $requestMock->shouldReceive('getContent')
            ->andReturn('{"id":"hook_aaaaaaaaaaaaaaaa"}');

        $validatorMock = Mockery::mock('alias:' . WebhookValidatorService::class);
        $validatorMock->shouldReceive('validateSignature')
            ->andReturn(true);

        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $webhookRecipientResponse = [
            'message' => 'Recipient updated',
            'code' => 200
        ];
        $webhookReceiverServiceMock->shouldReceive('handle')
            ->once()
            ->andReturn($webhookRecipientResponse);

        $webhookManagement = new WebhookManagement($orderFactoryMock, $accountMock, $webhookReceiverServiceMock, $requestMock);

        $id = "hook_aaaaaaaaaaaaaaaa";
        $type = "recipient.updated";
        $data = [
            "id" => 'rp_xxxxxxxxxxxxxxxx',
            "name" => "Test recipient",
            "email" => "test@recipient.test",
            "document" => "11111111111",
            "description" => "Test description",
            "type" => "individual",
            "payment_mode" => "bank_transfer",
            "status" => "active",
            "kyc_details" =>
            [
                "status" => "approved"
            ],
        ];

        $account = [
            "id" => "acc_xxxxxxxxxxxxxxxx",
            "name" => "Account Test"
        ];
        $result = $webhookManagement->save($id, $type, $data, $account);

        $this->assertSame($webhookRecipientResponse, $result);
    }


    public function testSaveWithNonPlatformWebhook()
    {
        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();


        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('loadByIncrementId')
            ->andReturnSelf();
        $orderMock->shouldReceive('getId')
            ->andReturnFalse();

        $orderFactoryMock = Mockery::mock(OrderFactory::class);
        $orderFactoryMock->shouldReceive('create')
            ->andReturn($orderMock);
            
        $accountMock = Mockery::mock(Account::class);

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getHeader')
            ->with(WebhookManagement::WEBHOOK_SIGNATURE_HEADER)
            ->andReturn('alg=RS256; kid=test-kid; signature=test-sig');
        $requestMock->shouldReceive('getContent')
            ->andReturn('{"id":"hook_aaaaaaaaaaaaaaaa"}');

        $validatorMock = Mockery::mock('alias:' . WebhookValidatorService::class);
        $validatorMock->shouldReceive('validateSignature')
            ->andReturn(true);

        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $expectedResponse = [
            'message' => 'Webhook Received',
            'code' => 200
        ];

        $webhookManagement = new WebhookManagement($orderFactoryMock, $accountMock, $webhookReceiverServiceMock, $requestMock);

        $id = "hook_aaaaaaaaaaaaaaaa";
        $type = "charge.paid";
        $data = [];

        $account = [
            "id" => "acc_xxxxxxxxxxxxxxxx",
            "name" => "Account Test"
        ];
        $result = $webhookManagement->save($id, $type, $data, $account);

        $this->assertSame($expectedResponse, $result);
    }

    public function testSaveDispatchesIdentifierMethodsWhenIdentifierIsProvidedAndReturnsHandlerResponse()
    {
        // Arrange
        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')->andReturnSelf();

        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('loadByIncrementId')->andReturnSelf();
        $orderMock->shouldReceive('getId')->andReturnFalse();

        $orderFactoryMock = Mockery::mock(OrderFactory::class);
        $orderFactoryMock->shouldReceive('create')->andReturn($orderMock);

        $identifier = [
            'payment_profile_id'        => 'pp_abc123',
            'point_of_interaction_type' => 'Ecommerce',
        ];

        $ppCallCount  = 0;
        $poiCallCount = 0;
        $accountMock  = Mockery::mock(Account::class);
        $accountMock->shouldReceive('savePaymentProfileIdFromWebhook')
            ->once()
            ->with($identifier)
            ->andReturnUsing(function () use (&$ppCallCount) { $ppCallCount++; });
        $accountMock->shouldReceive('savePoiTypeFromWebhook')
            ->once()
            ->with($identifier)
            ->andReturnUsing(function () use (&$poiCallCount) { $poiCallCount++; });

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getHeader')
            ->with(WebhookManagement::WEBHOOK_SIGNATURE_HEADER)
            ->andReturn('alg=RS256; kid=test-kid; signature=test-sig');
        $requestMock->shouldReceive('getContent')->andReturn('{"id":"hook_aaaaaaaaaaaaaaaa"}');

        $validatorMock = Mockery::mock('alias:' . WebhookValidatorService::class);
        $validatorMock->shouldReceive('validateSignature')->andReturn(true);

        $expectedResponse = ['message' => 'Recipient updated', 'code' => 200];
        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $webhookReceiverServiceMock->shouldReceive('handle')->once()->andReturn($expectedResponse);

        $webhookManagement = new WebhookManagement(
            $orderFactoryMock, $accountMock, $webhookReceiverServiceMock, $requestMock
        );

        // Act
        $result = $webhookManagement->save(
            'hook_aaaaaaaaaaaaaaaa',
            'recipient.updated',
            ['id' => 'rp_xxxxxxxxxxxxxxxx', 'name' => 'Test recipient'],
            ['id' => 'acc_xxxxxxxxxxxxxxxx'],
            $identifier
        );

        // Assert
        $this->assertSame(1, $ppCallCount, 'savePaymentProfileIdFromWebhook must be called exactly once');
        $this->assertSame(1, $poiCallCount, 'savePoiTypeFromWebhook must be called exactly once');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSaveSkipsIdentifierMethodsAndReturnsHandlerResponseWhenIdentifierIsNull()
    {
        // Arrange
        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')->andReturnSelf();

        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('loadByIncrementId')->andReturnSelf();
        $orderMock->shouldReceive('getId')->andReturnFalse();

        $orderFactoryMock = Mockery::mock(OrderFactory::class);
        $orderFactoryMock->shouldReceive('create')->andReturn($orderMock);

        $ppCallCount  = 0;
        $poiCallCount = 0;
        $accountMock  = Mockery::mock(Account::class);
        $accountMock->shouldReceive('savePaymentProfileIdFromWebhook')
            ->andReturnUsing(function () use (&$ppCallCount) { $ppCallCount++; });
        $accountMock->shouldReceive('savePoiTypeFromWebhook')
            ->andReturnUsing(function () use (&$poiCallCount) { $poiCallCount++; });

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getHeader')
            ->with(WebhookManagement::WEBHOOK_SIGNATURE_HEADER)
            ->andReturn('alg=RS256; kid=test-kid; signature=test-sig');
        $requestMock->shouldReceive('getContent')->andReturn('{"id":"hook_aaaaaaaaaaaaaaaa"}');

        $validatorMock = Mockery::mock('alias:' . WebhookValidatorService::class);
        $validatorMock->shouldReceive('validateSignature')->andReturn(true);

        $expectedResponse = ['message' => 'ok', 'code' => 200];
        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $webhookReceiverServiceMock->shouldReceive('handle')->once()->andReturn($expectedResponse);

        $webhookManagement = new WebhookManagement(
            $orderFactoryMock, $accountMock, $webhookReceiverServiceMock, $requestMock
        );

        // Act
        $result = $webhookManagement->save(
            'hook_aaaaaaaaaaaaaaaa',
            'recipient.updated',
            ['id' => 'rp_xxxxxxxxxxxxxxxx'],
            ['id' => 'acc_xxxxxxxxxxxxxxxx'],
            null
        );

        // Assert
        $this->assertSame(0, $ppCallCount, 'savePaymentProfileIdFromWebhook must not be called when identifier is null');
        $this->assertSame(0, $poiCallCount, 'savePoiTypeFromWebhook must not be called when identifier is null');
        $this->assertSame($expectedResponse, $result);
    }

    public function testSaveSkipsIdentifierMethodsAndReturnsHandlerResponseWhenIdentifierIsEmptyArray()
    {
        // Arrange
        $moduleCoreSetupMock = Mockery::mock('alias:Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup');
        $moduleCoreSetupMock->shouldReceive('bootstrap')->andReturnSelf();

        $orderMock = Mockery::mock(Order::class);
        $orderMock->shouldReceive('loadByIncrementId')->andReturnSelf();
        $orderMock->shouldReceive('getId')->andReturnFalse();

        $orderFactoryMock = Mockery::mock(OrderFactory::class);
        $orderFactoryMock->shouldReceive('create')->andReturn($orderMock);

        $ppCallCount  = 0;
        $poiCallCount = 0;
        $accountMock  = Mockery::mock(Account::class);
        $accountMock->shouldReceive('savePaymentProfileIdFromWebhook')
            ->andReturnUsing(function () use (&$ppCallCount) { $ppCallCount++; });
        $accountMock->shouldReceive('savePoiTypeFromWebhook')
            ->andReturnUsing(function () use (&$poiCallCount) { $poiCallCount++; });

        $requestMock = Mockery::mock(RequestInterface::class);
        $requestMock->shouldReceive('getHeader')
            ->with(WebhookManagement::WEBHOOK_SIGNATURE_HEADER)
            ->andReturn('alg=RS256; kid=test-kid; signature=test-sig');
        $requestMock->shouldReceive('getContent')->andReturn('{"id":"hook_aaaaaaaaaaaaaaaa"}');

        $validatorMock = Mockery::mock('alias:' . WebhookValidatorService::class);
        $validatorMock->shouldReceive('validateSignature')->andReturn(true);

        $expectedResponse = ['message' => 'ok', 'code' => 200];
        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $webhookReceiverServiceMock->shouldReceive('handle')->once()->andReturn($expectedResponse);

        $webhookManagement = new WebhookManagement(
            $orderFactoryMock, $accountMock, $webhookReceiverServiceMock, $requestMock
        );

        // Act
        $result = $webhookManagement->save(
            'hook_aaaaaaaaaaaaaaaa',
            'recipient.updated',
            ['id' => 'rp_xxxxxxxxxxxxxxxx'],
            ['id' => 'acc_xxxxxxxxxxxxxxxx'],
            []
        );

        // Assert
        $this->assertSame(0, $ppCallCount, 'savePaymentProfileIdFromWebhook must not be called when identifier is an empty array');
        $this->assertSame(0, $poiCallCount, 'savePoiTypeFromWebhook must not be called when identifier is an empty array');
        $this->assertSame($expectedResponse, $result);
    }
}
