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
     * Verifies that uninstallCommand deletes all 11 hub config paths,
     * including the two One Stone fields (payment_profile_id and poi_type),
     * and cleans the Magento config cache.
     */
    public function testUninstallCommandDeletesAllHubConfigPaths()
    {
        $scope = 'websites';
        $websiteId = 1;

        $this->hubControllerIndexMock
            ->shouldReceive('getScopeName')
            ->once()
            ->andReturn($scope);

        $paths = [
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

        foreach ($paths as $path) {
            $this->configWriterMock
                ->shouldReceive('delete')
                ->with($path, $scope, $websiteId)
                ->once();
        }

        $this->cacheManagerMock
            ->shouldReceive('clean')
            ->with([Config::TYPE_IDENTIFIER])
            ->once();

        $result = $this->hubCommand->uninstallCommand();

        $this->assertEquals('Hub uninstalled successfully', $result);
    }
}
