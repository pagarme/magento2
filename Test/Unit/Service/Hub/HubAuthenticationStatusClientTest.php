<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Hub;

use Magento\Framework\HTTP\Client\Curl;
use Mockery;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Service\Hub\AuthenticationNotFoundException;
use Pagarme\Pagarme\Service\Hub\HubAuthenticationStatusClient;
use Pagarme\Pagarme\Service\Hub\HubUnavailableException;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Psr\Log\LoggerInterface;

/**
 * @covers \Pagarme\Pagarme\Service\Hub\HubAuthenticationStatusClient
 */
class HubAuthenticationStatusClientTest extends BaseTest
{
    const TRANSACTION_ID = '6e5b0ca7-3f38-4432-85b8-23a391fc6e91';

    private $curl;

    private $config;

    private $client;

    protected function setUp(): void
    {
        $this->curl = Mockery::mock(Curl::class)->shouldIgnoreMissing();
        $this->config = Mockery::mock(Config::class);
        $this->config->shouldReceive('getSecretKey')->andReturn('sk_test_123');
        $this->config->shouldReceive('isSandboxMode')->andReturn(true);
        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->client = new HubAuthenticationStatusClient($this->curl, $this->config, $logger);
    }

    private function hubAnswers($status, $body)
    {
        $this->curl->shouldReceive('getStatus')->andReturn($status);
        $this->curl->shouldReceive('getBody')->andReturn(is_array($body) ? json_encode($body) : $body);
    }

    private function approvedBody(array $overrides = [])
    {
        return array_merge([
            'tds_server_trans_id' => self::TRANSACTION_ID,
            'protocol_version' => '2.2.0',
            'tds_method_indicator' => 'Y',
        ], $overrides);
    }

    public function testReturnsTheTdsMethodIndicatorAsTheFinalStatus()
    {
        $this->hubAnswers(200, $this->approvedBody());

        $this->assertSame('Y', $this->client->getStatus(self::TRANSACTION_ID));
    }

    public function testCallsTheStatusEndpointWithSecretKeyAndEnvironment()
    {
        $this->hubAnswers(200, $this->approvedBody());
        $this->curl->shouldReceive('setCredentials')->once()->with('sk_test_123', '');
        $this->curl->shouldReceive('addHeader')->once()->with('X-Hub-Environment', 'test');
        $this->curl->shouldReceive('get')->once()->with(
            'https://hubapi.pagar.me/v2/management/authentications/status/' . self::TRANSACTION_ID
        );

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    public function testNotFoundBecomesAuthenticationNotFound()
    {
        $this->hubAnswers(404, [
            'error' => ['code' => 'NOT_FOUND', 'message' => 'not found', 'request_id' => 'req-1'],
        ]);

        $this->expectException(AuthenticationNotFoundException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    public function testNotFoundWithAnUnreadableBodyIsStillNotFound()
    {
        $this->hubAnswers(404, '<html>');

        $this->expectException(AuthenticationNotFoundException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    /**
     * @dataProvider unavailableStatuses
     */
    public function testAnyOtherHttpStatusMeansTheHubIsUnavailable($httpStatus)
    {
        $this->hubAnswers($httpStatus, '');

        $this->expectException(HubUnavailableException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    public static function unavailableStatuses()
    {
        return ['unauthorized' => [401], 'forbidden' => [403], 'server error' => [500], 'no response' => [0]];
    }

    public function testAnotherTransactionInTheAnswerIsRejected()
    {
        $this->hubAnswers(200, $this->approvedBody(['tds_server_trans_id' => '11111111-1111-1111-1111-111111111111']));

        $this->expectException(HubUnavailableException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    /**
     * @dataProvider unusableBodies
     */
    public function testAnUnusable200BodyIsRejected($body)
    {
        $this->hubAnswers(200, $body);

        $this->expectException(HubUnavailableException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }

    public static function unusableBodies()
    {
        return [
            'not json' => ['oops'],
            'no indicator' => [['tds_server_trans_id' => self::TRANSACTION_ID]],
            'empty indicator' => [['tds_server_trans_id' => self::TRANSACTION_ID, 'tds_method_indicator' => '']],
        ];
    }

    public function testATransportFailureMeansTheHubIsUnavailable()
    {
        $this->curl->shouldReceive('get')->andThrow(new \Exception('Could not resolve host'));

        $this->expectException(HubUnavailableException::class);

        $this->client->getStatus(self::TRANSACTION_ID);
    }
}
