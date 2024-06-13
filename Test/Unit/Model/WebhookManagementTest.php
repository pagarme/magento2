<?php

namespace Pagarme\Pagarme\Test\Unit\Model;

use Mockery;
use Magento\Sales\Model\Order;
use Pagarme\Pagarme\Model\Account;
use Magento\Sales\Model\OrderFactory;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Model\WebhookManagement;
use Pagarme\Core\Webhook\Services\WebhookReceiverService;

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
        
        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $webhookRecipientResponse = [
            'message' => 'Recipient updated',
            'code' => 200
        ];
        $webhookReceiverServiceMock->shouldReceive('handle')
            ->once()
            ->andReturn($webhookRecipientResponse);

        $webhookManagement = new WebhookManagement($orderFactoryMock, $accountMock, $webhookReceiverServiceMock);

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
        
        $webhookReceiverServiceMock = Mockery::mock(WebhookReceiverService::class);
        $expectedResponse = [
            'message' => 'Webhook Received',
            'code' => 200
        ];

        $webhookManagement = new WebhookManagement($orderFactoryMock, $accountMock, $webhookReceiverServiceMock);

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
}
