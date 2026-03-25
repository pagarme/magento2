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
