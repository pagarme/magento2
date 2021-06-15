<?php

namespace Pagarme\Pagarme\Model\Api;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Webapi\Exception as MagentoException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Api\HubCommandInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

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

    public function __construct(
        Request $request,
        WriterInterface $configWriter,
        Manager $cacheManager,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $params = json_decode(
            json_encode($this->request->getBodyParams())
        );

        $paramsFromUrl = $this->request->getParams();
        $this->websiteId = isset($paramsFromUrl['websiteId'])
            ? $paramsFromUrl['websiteId']
            : $this->storeManager->getDefaultStoreView()->getWebsiteId();

        $this->storeManager->setCurrentStore($this->websiteId);
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

        $commandMessage = $this->$command();
        return $commandMessage;
    }

    public function uninstallCommand()
    {
        $this->configWriter->save(
            "pagarme_pagarme/hub/install_id",
            null,
            'websites',
            $this->websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/hub/access_token",
            null,
            'websites',
            $this->websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/secret_key",
            null,
            'websites',
            $this->websiteId
        );

        $this->configWriter->save(
            "pagarme_pagarme/global/public_key",
            null,
            'websites',
            $this->websiteId
        );

        $this->cacheManager->clean(['config']);

        return "Hub uninstalled successfully";
    }
}
