<?php

namespace Pagarme\Pagarme\Test\Unit\Model;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Pagarme\Controller\Adminhtml\Hub\Index as HubControllerIndex;
use Pagarme\Pagarme\Model\Account;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Pagarme\Pagarme\Service\AccountService;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Magento\Backend\Model\Session;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AccountTest
 *
 * Unit tests for the Account class methods
 */
class AccountTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var WriterInterface|Mockery\MockInterface
     */
    private $configWriterMock;

    /**
     * @var StoreManagerInterface|Mockery\MockInterface
     */
    private $storeManagerMock;

    /**
     * @var AccountService|Mockery\MockInterface
     */
    private $accountServiceMock;

    /**
     * @var \Pagarme\Pagarme\Model\Api\HubCommand|Mockery\MockInterface
     */
    private $hubCommandMock;

    /**
     * @var CollectionFactory|Mockery\MockInterface
     */
    private $configCollectionFactoryMock;

    /**
     * @var LoggerInterface|Mockery\MockInterface
     */
    private $loggerMock;

    /**
     * @var Session|Mockery\MockInterface
     */
    private $sessionMock;

    /**
     * @var PagarmeConfigProvider|Mockery\MockInterface
     */
    private $pagarmeConfigProviderMock;

    /**
     * @var HubControllerIndex|Mockery\MockInterface
     */
    private $hubControllerIndexMock;

    /**
     * @var ConfigurationStub
     */
    private $configMock;

    /**
     * Setup before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configWriterMock = Mockery::mock(WriterInterface::class);
        $this->storeManagerMock = Mockery::mock(StoreManagerInterface::class);
        $this->accountServiceMock = Mockery::mock(AccountService::class);
        $this->hubCommandMock = Mockery::mock(\Pagarme\Pagarme\Model\Api\HubCommand::class);
        $this->configCollectionFactoryMock = Mockery::mock(CollectionFactory::class);
        $this->loggerMock = Mockery::mock(LoggerInterface::class);
        $this->sessionMock = Mockery::mock(Session::class);
        $this->pagarmeConfigProviderMock = Mockery::mock(PagarmeConfigProvider::class);
        $this->hubControllerIndexMock = Mockery::mock(HubControllerIndex::class);

        $this->configMock = new ConfigurationStub();

        $this->account = new Account(
            $this->configWriterMock,
            $this->storeManagerMock,
            $this->accountServiceMock,
            $this->hubCommandMock,
            $this->configCollectionFactoryMock,
            $this->loggerMock,
            $this->sessionMock,
            $this->pagarmeConfigProviderMock,
            $this->hubControllerIndexMock
        );

        $this->injectConfigProperty($this->configMock);
    }

    /**
     * Test getPaymentProfileId method when a profileId is configured
     */
    public function testGetPaymentProfileIdReturnsProfileId()
    {
        // Arrange
        $expectedProfileId = 'pp_test_123456';
        $this->configMock->setPaymentProfileId($expectedProfileId);

        // Act
        $result = $this->account->getPaymentProfileId();

        // Assert
        $this->assertEquals($expectedProfileId, $result);
    }

    /**
     * Test getPaymentProfileId method when profileId is not configured
     */
    public function testGetPaymentProfileIdReturnsNullWhenNotConfigured()
    {
        // Act
        $result = $this->account->getPaymentProfileId();

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getPoiType method when a poiType is configured
     */
    public function testGetPoiTypeReturnsPoiTypeArray()
    {
        // Arrange
        $expectedPoiType = ['type' => 'physical', 'location' => 'store'];
        $this->configMock->setPoiType($expectedPoiType);

        // Act
        $result = $this->account->getPoiType();

        // Assert
        $this->assertEquals($expectedPoiType, $result);
    }

    /**
     * Test getPoiType method when poiType is not configured
     */
    public function testGetPoiTypeReturnsNullWhenNotConfigured()
    {
        // Act
        $result = $this->account->getPoiType();

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test isOneStoneEnabled method when paymentProfileId is configured
     */
    public function testIsOneStoneEnabledReturnsTrueWhenProfileIdExists()
    {
        // Arrange
        $this->configMock->setPaymentProfileId('pp_test_123456');

        // Act
        $result = $this->account->isOneStoneEnabled();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * Test isOneStoneEnabled method when paymentProfileId is not configured
     */
    public function testIsOneStoneEnabledReturnsFalseWhenProfileIdIsNull()
    {
        // Act
        $result = $this->account->isOneStoneEnabled();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test isOneStoneEnabled method when paymentProfileId is an empty string
     */
    public function testIsOneStoneEnabledReturnsFalseWhenProfileIdIsEmpty()
    {
        // Arrange
        $this->configMock->setPaymentProfileId('');

        // Act
        $result = $this->account->isOneStoneEnabled();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * Test getDashUrl returns URL with PaymentProfileId when One Stone is enabled
     */
    public function testGetDashUrlUsesPaymentProfileIdWhenOneStoneEnabled()
    {
        // Arrange
        $this->configMock->setPaymentProfileId('pp_xxxxxxxxxxxx');
        $this->configMock->setMerchantId('merch_yyyyyyyyyyyy');

        // Act
        $result = $this->account->getDashUrl();

        // Assert
        $this->assertSame('https://dash.stone.com.br/pp_xxxxxxxxxxxx', $result);
    }

    /**
     * Test getDashUrl returns URL with AccountId when One Stone is disabled (legacy mode)
     */
    public function testGetDashUrlUsesAccountIdWhenOneStoneDisabled()
    {
        // Arrange
        $this->configMock->setPaymentProfileId(null);
        $this->configMock->setAccountId('acc_zzzzzzzzzzzz');
        $this->configMock->setMerchantId('merch_yyyyyyyyyyyy');

        // Act
        $result = $this->account->getDashUrl();

        // Assert
        $this->assertSame('https://dash.pagar.me/merch_yyyyyyyyyyyy/acc_zzzzzzzzzzzz/', $result);
    }

    /**
     * Test getDashUrl returns null when MerchantId is absent
     */
    public function testGetDashUrlReturnsNullWhenMerchantIdAbsent()
    {
        // Arrange
        $this->configMock->setPaymentProfileId(null);
        $this->configMock->setAccountId('acc_zzzzzzzzzzzz');
        $this->configMock->setMerchantId(null);

        // Act
        $result = $this->account->getDashUrl();

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getDashUrl returns null when AccountId is absent in legacy mode
     */
    public function testGetDashUrlReturnsNullWhenAccountIdAbsentInLegacyMode()
    {
        // Arrange
        $this->configMock->setPaymentProfileId(null);
        $this->configMock->setAccountId(null);
        $this->configMock->setMerchantId('merch_yyyyyyyyyyyy');

        // Act
        $result = $this->account->getDashUrl();

        // Assert
        $this->assertNull($result);
    }

    // ─── savePaymentProfileIdFromWebhook ────────────────────────────────────────

    public function testSavePaymentProfileIdFromWebhookDoesNotSaveWhenProfileIdAlreadyExists()
    {
        // Arrange
        $this->configMock->setPaymentProfileId('pp_existing_123');
        $identifier = [
            'payment_profile_id'        => 'pp_new_456',
            'point_of_interaction_type' => 'Ecommerce',
        ];
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook($identifier);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when paymentProfileId is already set');
    }

    public function testSavePaymentProfileIdFromWebhookDoesNotSaveWhenIdentifierIsNull()
    {
        // Arrange
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook(null);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when identifier is null');
    }

    public function testSavePaymentProfileIdFromWebhookDoesNotSaveWhenPoiTypeIsEmpty()
    {
        // Arrange
        $identifier = [
            'payment_profile_id'        => 'pp_new_456',
            'point_of_interaction_type' => '',
        ];
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook($identifier);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when point_of_interaction_type is empty');
    }

    public function testSavePaymentProfileIdFromWebhookDoesNotSaveWhenPoiTypeIsNotEcommerce()
    {
        // Arrange
        $identifier = [
            'payment_profile_id'        => 'pp_new_456',
            'point_of_interaction_type' => 'Pos',
        ];
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook($identifier);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when point_of_interaction_type is not Ecommerce');
    }

    public function testSavePaymentProfileIdFromWebhookPersistsWithCorrectArgumentsForEcommerceIdentifier()
    {
        // Arrange
        $paymentProfileId = 'pp_abc123';
        $websiteId        = 1;
        $scopeName        = 'websites';
        $identifier = [
            'payment_profile_id'        => $paymentProfileId,
            'point_of_interaction_type' => 'Ecommerce',
        ];

        $storeMock = Mockery::mock();
        $storeMock->shouldReceive('getWebsiteId')->andReturn($websiteId);
        $this->storeManagerMock->shouldReceive('getStore')->andReturn($storeMock);
        $this->hubControllerIndexMock->shouldReceive('getScopeName')->andReturn($scopeName);

        $capturedArgs = [];
        $this->configWriterMock
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook($identifier);

        // Assert
        $this->assertSame(PagarmeConfigProvider::PATH_PAYMENT_PROFILE_ID, $capturedArgs[0]);
        $this->assertSame($paymentProfileId, $capturedArgs[1]);
        $this->assertSame($scopeName, $capturedArgs[2]);
        $this->assertSame($websiteId, $capturedArgs[3]);
    }

    public function testSavePaymentProfileIdFromWebhookPoiTypeComparisonIsCaseInsensitive()
    {
        // Arrange
        $paymentProfileId = 'pp_abc123';
        $websiteId        = 1;
        $scopeName        = 'websites';
        $identifier = [
            'payment_profile_id'        => $paymentProfileId,
            'point_of_interaction_type' => 'ECOMMERCE', // uppercase variant
        ];

        $storeMock = Mockery::mock();
        $storeMock->shouldReceive('getWebsiteId')->andReturn($websiteId);
        $this->storeManagerMock->shouldReceive('getStore')->andReturn($storeMock);
        $this->hubControllerIndexMock->shouldReceive('getScopeName')->andReturn($scopeName);

        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePaymentProfileIdFromWebhook($identifier);

        // Assert
        $this->assertSame(1, $saveInvocations, 'configWriter->save must be called even when point_of_interaction_type is uppercase');
    }

    // ─── savePoiTypeFromWebhook ──────────────────────────────────────────────────

    public function testSavePoiTypeFromWebhookDoesNotSaveWhenPoiTypeAlreadySet()
    {
        // Arrange
        $this->configMock->setPoiType(['type' => 'Ecommerce']);
        $identifier = [
            'payment_profile_id'        => 'pp_new_456',
            'point_of_interaction_type' => 'Ecommerce',
        ];
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePoiTypeFromWebhook($identifier);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when poiType is already persisted');
    }

    public function testSavePoiTypeFromWebhookDoesNotSaveWhenIdentifierIsNull()
    {
        // Arrange
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePoiTypeFromWebhook(null);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when identifier is null');
    }

    public function testSavePoiTypeFromWebhookDoesNotSaveWhenPoiTypeIsNotEcommerce()
    {
        // Arrange
        $identifier = [
            'payment_profile_id'        => 'pp_new_456',
            'point_of_interaction_type' => 'Tef',
        ];
        $saveInvocations = 0;
        $this->configWriterMock
            ->shouldReceive('save')
            ->andReturnUsing(function () use (&$saveInvocations) {
                $saveInvocations++;
            });

        // Act
        $this->account->savePoiTypeFromWebhook($identifier);

        // Assert
        $this->assertSame(0, $saveInvocations, 'configWriter->save must not be called when point_of_interaction_type is not Ecommerce');
    }

    public function testSavePoiTypeFromWebhookPersistsStringValueWithCorrectArgumentsForEcommerceIdentifier()
    {
        // Arrange
        $poiTypeValue = 'Ecommerce';
        $websiteId    = 1;
        $scopeName    = 'websites';
        $identifier = [
            'payment_profile_id'        => 'pp_abc123',
            'point_of_interaction_type' => $poiTypeValue,
        ];

        $storeMock = Mockery::mock();
        $storeMock->shouldReceive('getWebsiteId')->andReturn($websiteId);
        $this->storeManagerMock->shouldReceive('getStore')->andReturn($storeMock);
        $this->hubControllerIndexMock->shouldReceive('getScopeName')->andReturn($scopeName);

        $capturedArgs = [];
        $this->configWriterMock
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(function () use (&$capturedArgs) {
                $capturedArgs = func_get_args();
            });

        // Act
        $this->account->savePoiTypeFromWebhook($identifier);

        // Assert
        $this->assertSame(PagarmeConfigProvider::PATH_POI_TYPE, $capturedArgs[0]);
        $this->assertSame($poiTypeValue, $capturedArgs[1]);
        $this->assertSame($scopeName, $capturedArgs[2]);
        $this->assertSame($websiteId, $capturedArgs[3]);
    }



    /**
     * Inject the config property via reflection to facilitate testing
     *
     * @param ConfigurationStub|Configuration $config
     * @return void
     */
    private function injectConfigProperty($config)
    {
        $reflection = new \ReflectionClass($this->account);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $property->setValue($this->account, $config);
    }
}
