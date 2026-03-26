<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Api\HubCommandInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Controller\Adminhtml\Hub\Index as HubControllerIndex;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class HubCommand implements HubCommandInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var Manager
     */
    protected $cacheManager;

    /**
     * @var string
     */
    protected $websiteId;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var HubControllerIndex
     */
    private $hubControllerIndex;

    public function __construct(
        Request $request,
        WriterInterface $configWriter,
        Manager $cacheManager,
        StoreManagerInterface $storeManager,
        HubControllerIndex $hubControllerIndex
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->storeManager = $storeManager;
        $this->hubControllerIndex = $hubControllerIndex;
    }

    /**
     * @throws MagentoException
     * @throws \Exception
     */
    public function execute()
    {
        $params = json_decode(
            json_encode($this->request->getBodyParams())
        );

        $paramsFromUrl = $this->request->getParams();
        $this->websiteId = $paramsFromUrl['websiteId'] ?? 0;

        Magento2CoreSetup::bootstrap();

        $hubIntegrationService = new HubIntegrationService();
        try {
            $hubIntegrationService->executeCommandFromPost($params);
        } catch (\Throwable $e) {
            throw new MagentoException(
                __($e->getMessage()),
                0,
                400
            );
        }

        $command = strtolower($params->command) . 'Command';

        if (!method_exists($this, $command)) {
            return "Command $params->command executed successfully";
        }

        return $this->$command();
    }

    public function uninstallCommand()
    {
        $scope = $this->hubControllerIndex->getScopeName();
        $websiteId = $this->websiteId ?? Magento2CoreSetup::getCurrentStoreId();

        if (!$websiteId) {
            $websiteId = 0;
        }

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_INSTALL_ID,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_ENVIRONMENT,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_SECRET_KEY,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_PUBLIC_KEY,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_SECRET_KEY_TEST,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_PUBLIC_KEY_TEST,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_ACCOUNT_ID,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_MERCHANT_ID,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_PAYMENT_PROFILE_ID,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_POI_TYPE,
            $scope,
            $websiteId
        );

        $this->configWriter->delete(
            PagarmeConfigProvider::PATH_DASH_ERRORS,
            $scope,
            $websiteId
        );

        $this->cacheManager->clean([Config::TYPE_IDENTIFIER]);

        return "Hub uninstalled successfully";
    }
}
