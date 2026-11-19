<?php

namespace Pagarme\Pagarme\Test\Unit\Service\Payment;

use Magento\Framework\Exception\LocalizedException;
use Mockery;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\Config;
use Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config\Config as CreditCardConfig;
use Pagarme\Pagarme\Service\Hub\AuthenticationNotFoundException;
use Pagarme\Pagarme\Service\Hub\AuthenticationStatusProviderInterface;
use Pagarme\Pagarme\Service\Hub\HubUnavailableException;
use Pagarme\Pagarme\Service\Payment\ThreeDSAuthenticationStatusValidator;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Psr\Log\LoggerInterface;

/**
 * @covers \Pagarme\Pagarme\Service\Payment\ThreeDSAuthenticationStatusValidator
 */
class ThreeDSAuthenticationStatusValidatorTest extends BaseTest
{
    const TRANSACTION_ID = '6e5b0ca7-3f38-4432-85b8-23a391fc6e91';

    const STATUSES = ['Y', 'A', 'N', 'C', 'U', 'R', 'I'];

    private $provider;

    private $config;

    private $creditCardConfig;

    private $validator;

    protected function setUp(): void
    {
        $this->provider = Mockery::mock(AuthenticationStatusProviderInterface::class);
        $this->config = Mockery::mock(Config::class);
        $this->config->shouldReceive('isSandboxMode')->andReturn(false)->byDefault();
        $this->creditCardConfig = Mockery::mock(CreditCardConfig::class);
        $this->creditCardConfig->shouldReceive('getTdsActive')->andReturn(true)->byDefault();
        $logger = Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->validator = new ThreeDSAuthenticationStatusValidator(
            $this->provider,
            $this->config,
            $this->creditCardConfig,
            $logger
        );
    }

    private function authentication($transactionId, $status)
    {
        return json_encode(['tds_server_trans_id' => $transactionId, 'trans_status' => $status]);
    }

    /**
     * @dataProvider payloadsWithoutAuthenticationClaim
     */
    public function testDoesNotCallTheHubWhenThereIsNothingToVerify($authentication)
    {
        $this->provider->shouldNotReceive('getStatus');

        $this->validator->assertStatusMatchesHub($authentication);

        $this->addToAssertionCount(1);
    }

    public static function payloadsWithoutAuthenticationClaim()
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'empty object' => ['{}'],
            'transaction id without status' => [json_encode(['tds_server_trans_id' => self::TRANSACTION_ID])],
        ];
    }

    public function testAcceptsWhenTheHubStatusEqualsTheClientStatus()
    {
        $this->provider->shouldReceive('getStatus')->once()->with(self::TRANSACTION_ID)->andReturn('Y');

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, 'Y'));

        $this->addToAssertionCount(1);
    }

    public function testRejectsWhenTheHubStatusDiffersFromTheClientStatus()
    {
        $this->provider->shouldReceive('getStatus')->once()->with(self::TRANSACTION_ID)->andReturn('N');

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, 'Y'));
    }

    public function testRejectsAStatusClaimedWithoutTransactionId()
    {
        $this->provider->shouldNotReceive('getStatus');

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub(json_encode(['trans_status' => 'Y']));
    }

    public function testRejectsMalformedJson()
    {
        $this->provider->shouldNotReceive('getStatus');

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub('{not json');
    }

    /**
     * @dataProvider transactionIdsInAnyFormat
     */
    public function testSendsTheTransactionIdToTheHubWithoutJudgingItsFormat($transactionId)
    {
        $this->provider->shouldReceive('getStatus')->once()->with($transactionId)->andReturn('Y');

        $this->validator->assertStatusMatchesHub($this->authentication($transactionId, 'Y'));

        $this->addToAssertionCount(1);
    }

    public static function transactionIdsInAnyFormat()
    {
        return [
            'uuid' => [self::TRANSACTION_ID],
            'not a uuid' => ['abc-123'],
            'numeric string' => ['123456'],
        ];
    }

    /**
     * @dataProvider unusableTransactionIds
     */
    public function testRejectsAStatusWhoseTransactionIdIsMissingOrNotText($transactionId)
    {
        $this->provider->shouldNotReceive('getStatus');

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub($this->authentication($transactionId, 'Y'));
    }

    public static function unusableTransactionIds()
    {
        return [
            'null' => [null],
            'empty' => [''],
            'array' => [['x']],
            'number' => [123],
        ];
    }

    public function testRejectsWhenTheAuthenticationDoesNotExistAtTheHub()
    {
        $this->provider->shouldReceive('getStatus')->andThrow(new AuthenticationNotFoundException('not found'));

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, 'Y'));
    }

    public function testRejectsWhenTheHubCannotBeReached()
    {
        $this->provider->shouldReceive('getStatus')->andThrow(new HubUnavailableException('timeout'));

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, 'Y'));
    }

    /**
     * @dataProvider everyStatus
     */
    public function testAcceptsAnyStatusAsLongAsTheHubReturnsTheSameOne($status)
    {
        $this->provider->shouldReceive('getStatus')->once()->with(self::TRANSACTION_ID)->andReturn($status);

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, $status));

        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider everyStatusPair
     */
    public function testRejectsAnyStatusThatDiffersFromTheHubOne($claimedStatus, $hubStatus)
    {
        $this->provider->shouldReceive('getStatus')->once()->with(self::TRANSACTION_ID)->andReturn($hubStatus);

        $this->expectException(LocalizedException::class);

        $this->validator->assertStatusMatchesHub($this->authentication(self::TRANSACTION_ID, $claimedStatus));
    }

    public static function everyStatus()
    {
        return array_combine(self::STATUSES, array_map(function ($status) {
            return [$status];
        }, self::STATUSES));
    }

    public static function everyStatusPair()
    {
        $pairs = [];
        foreach (self::STATUSES as $claimed) {
            foreach (self::STATUSES as $hub) {
                if ($claimed !== $hub) {
                    $pairs["claimed $claimed, hub $hub"] = [$claimed, $hub];
                }
            }
        }

        return $pairs;
    }

    /**
     * @dataProvider payloadsThatWouldOtherwiseBeRejected
     */
    public function testSkipsTheVerificationInSandbox($authentication)
    {
        $this->config->shouldReceive('isSandboxMode')->andReturn(true);
        $this->provider->shouldNotReceive('getStatus');

        $this->validator->assertStatusMatchesHub($authentication);

        $this->addToAssertionCount(1);
    }

    public static function payloadsThatWouldOtherwiseBeRejected()
    {
        return [
            'valid payload' => [json_encode(['tds_server_trans_id' => self::TRANSACTION_ID, 'trans_status' => 'Y'])],
            'status without transaction id' => [json_encode(['trans_status' => 'Y'])],
            'malformed json' => ['{not json'],
        ];
    }

    /**
     * @dataProvider payloadsThatWouldOtherwiseBeRejected
     */
    public function testSkipsTheVerificationWhenThreeDSIsDisabled($authentication)
    {
        $this->creditCardConfig->shouldReceive('getTdsActive')->andReturn(false);
        $this->provider->shouldNotReceive('getStatus');

        $this->validator->assertStatusMatchesHub($authentication);

        $this->addToAssertionCount(1);
    }
}
