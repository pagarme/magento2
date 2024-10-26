<?php

namespace Pagarme\Pagarme\Test\Unit\Model\Api;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Mockery;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Pagarme\Pagarme\Model\Api\Subscription;
use Pagarme\Core\Recurrence\Aggregates\Subscription as SubscriptionModel;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SubscriptionTest extends BaseTest
{
    public function testListShouldReturnAllSubscriptions()
    {
        $subscriptionServiceMock = Mockery::mock('overload:Pagarme\Core\Recurrence\Services\SubscriptionService');
        $magento2CoreSetupMock = Mockery::mock('alias:Pagarme\Pagarme\Concrete\Magento2CoreSetup');
        $magento2CoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $subscriptionModel = new SubscriptionModel();
        $subscriptionModel->setId("123");
        $subscriptionModels = [$subscriptionModel];

        $subscriptionServiceMock->shouldReceive('listAll')
            ->andReturn($subscriptionModels);

        $stateMock = Mockery::mock(State::class);
        $stateMock->shouldReceive('getAreaCode')
            ->andReturn(Area::AREA_WEBAPI_REST);
        
        $subscription = new Subscription($stateMock);
        
        $expectedResult = json_decode(json_encode($subscriptionModels), true);

        $result = $subscription->list();

        $this->assertSame($result, $expectedResult);
    }

    public function testCancelShouldReturnSucessArray()
    {
        $subscriptionServiceMock = Mockery::mock('overload:Pagarme\Core\Recurrence\Services\SubscriptionService');
        $magento2CoreSetupMock = Mockery::mock('alias:Pagarme\Pagarme\Concrete\Magento2CoreSetup');
        $magento2CoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $expectedResult = [
            "message" => 'Subscription canceled with success!',
            "code" => 200
        ];
        $subscriptionServiceMock->shouldReceive('cancel')
            ->andReturn($expectedResult);

        $stateMock = Mockery::mock(State::class);
        $stateMock->shouldReceive('getAreaCode')
            ->andReturn(Area::AREA_WEBAPI_REST);
        
        $subscription = new Subscription($stateMock);

        $id = '123';
        $result = $subscription->cancel($id);

        $this->assertSame($result, $expectedResult);
    }

    public function testCancelShouldReturnErrorArray()
    {
        $subscriptionServiceMock = Mockery::mock('overload:Pagarme\Core\Recurrence\Services\SubscriptionService');
        $magento2CoreSetupMock = Mockery::mock('alias:Pagarme\Pagarme\Concrete\Magento2CoreSetup');
        $magento2CoreSetupMock->shouldReceive('bootstrap')
            ->andReturnSelf();

        $expectedResult = [
            "code" => 400,
            "message" => 'Error while canceling subscription',
        ];

        $exception = new Exception('Error while canceling subscription', 400);
        $subscriptionServiceMock->shouldReceive('cancel')
            ->andThrow($exception);

        $stateMock = Mockery::mock(State::class);
        $stateMock->shouldReceive('getAreaCode')
            ->andReturn(Area::AREA_WEBAPI_REST);
        
        $subscription = new Subscription($stateMock);

        $id = '123';
        $result = $subscription->cancel($id);

        $this->assertSame($result, $expectedResult);
    }
}
