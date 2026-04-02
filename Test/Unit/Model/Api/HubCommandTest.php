<?php

namespace Pagarme\Pagarme\Test\Unit\Model\Api;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Controller\Adminhtml\Hub\Index as HubControllerIndex;
use Pagarme\Pagarme\Model\Api\HubCommand;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;
use Pagarme\Pagarme\Test\Unit\BaseTest;

/**
 * Class HubCommandTest
 *
 * Unit tests for HubCommand::uninstallCommand()
 */
class HubCommandTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    /**
     * @var HubCommand
     */
    private $hubCommand;

    /**
     * @var WriterInterface|Mockery\MockInterface
     */
    private $configWriterMock;

    /**
     * @var Manager|Mockery\MockInterface
     */
    private $cacheManagerMock;

    /**
     * @var HubControllerIndex|Mockery\MockInterface
     */
    private $hubControllerIndexMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configWriterMock = Mockery::mock(WriterInterface::class);
        $this->cacheManagerMock = Mockery::mock(Manager::class);
        $this->hubControllerIndexMock = Mockery::mock(HubControllerIndex::class);

        $this->hubCommand = new HubCommand(
            Mockery::mock(Request::class),
            $this->configWriterMock,
            $this->cacheManagerMock,
            Mockery::mock(StoreManagerInterface::class),
            $this->hubControllerIndexMock
        );

        // Inject websiteId to avoid static call to Magento2CoreSetup::getCurrentStoreId()
        $websiteIdProp = new \ReflectionProperty(HubCommand::class, 'websiteId');
        $websiteIdProp->setAccessible(true);
        $websiteIdProp->setValue($this->hubCommand, 1);
    }

    /**
     * Verifies that uninstallCommand deletes all 11 relevant configuration paths
     * used by the hub integration, including the two One Stone fields
     * (payment_profile_id and poi_type), and cleans the Magento config cache.
     */
    public function testUninstallCommandDeletesAllHubConfigPaths()
    {
        // Arrange
        $scope     = 'websites';
        $websiteId = 1;

        $this->hubControllerIndexMock
            ->shouldReceive('getScopeName')
            ->once()
            ->andReturn($scope);

        foreach ($this->allHubPaths() as $path) {
            $this->configWriterMock
                ->shouldReceive('delete')
                ->with($path, $scope, $websiteId)
                ->once();
        }

        $this->cacheManagerMock
            ->shouldReceive('clean')
            ->with([Config::TYPE_IDENTIFIER])
            ->once();

        // Act
        $result = $this->hubCommand->uninstallCommand();

        // Assert
        $this->assertEquals('Hub uninstalled successfully', $result);
    }

    /**
     * Verifies that uninstallCommand uses websiteId = 0 when the request carries no
     * website context (global scope uninstall). The conditional block normalises any
     * falsy websiteId to 0, so all deletes must still fire with that value.
     */
    public function testUninstallCommandDeletesAllHubConfigPathsForDefaultScope()
    {
        // Arrange — inject websiteId = 0 to simulate a global-scope uninstall request
        $websiteIdProp = new \ReflectionProperty(HubCommand::class, 'websiteId');
        $websiteIdProp->setAccessible(true);
        $websiteIdProp->setValue($this->hubCommand, 0);

        $scope     = 'default';
        $websiteId = 0;

        $this->hubControllerIndexMock
            ->shouldReceive('getScopeName')
            ->once()
            ->andReturn($scope);

        foreach ($this->allHubPaths() as $path) {
            $this->configWriterMock
                ->shouldReceive('delete')
                ->with($path, $scope, $websiteId)
                ->once();
        }

        $this->cacheManagerMock
            ->shouldReceive('clean')
            ->with([Config::TYPE_IDENTIFIER])
            ->once();

        // Act
        $result = $this->hubCommand->uninstallCommand();

        // Assert
        $this->assertEquals('Hub uninstalled successfully', $result);
    }

    /**
     * @return string[]
     */
    private function allHubPaths(): array
    {
        return [
            PagarmeConfigProvider::PATH_INSTALL_ID,
            PagarmeConfigProvider::PATH_ENVIRONMENT,
            PagarmeConfigProvider::PATH_SECRET_KEY,
            PagarmeConfigProvider::PATH_PUBLIC_KEY,
            PagarmeConfigProvider::PATH_SECRET_KEY_TEST,
            PagarmeConfigProvider::PATH_PUBLIC_KEY_TEST,
            PagarmeConfigProvider::PATH_ACCOUNT_ID,
            PagarmeConfigProvider::PATH_MERCHANT_ID,
            PagarmeConfigProvider::PATH_PAYMENT_PROFILE_ID,
            PagarmeConfigProvider::PATH_POI_TYPE,
            PagarmeConfigProvider::PATH_DASH_ERRORS,
        ];
    }
}
