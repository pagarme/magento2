<?php

namespace Pagarme\Pagarme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Pagarme\Pagarme\Model\Api\HubCommand;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

class HubIntegrationObserver implements ObserverInterface
{
    /**
     * Contains the config provider for Pagar.me
     *
     * @var PagarmeConfigProvider
     */
    protected $hubCommand;

    public function __construct(HubCommand $hubCommand)
    {
        $this->hubCommand = $hubCommand;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $configData = $observer->getData('event')->getData('configData');

        if ($configData[ScopeInterface::SCOPE_WEBSITE] === null) {
            return;
        }

        $pagarmeGlobalFields = $configData['groups']['pagarme_pagarme']['groups']['pagarme_pagarme_global']['fields'];
        $integrationUseDefault = 0;

        if (array_key_exists('hub_integration', $pagarmeGlobalFields)) {
            $integrationUseDefault = $pagarmeGlobalFields['hub_integration']['inherit'];
        }

        if ($integrationUseDefault === '1') {
            $this->hubCommand->uninstallCommand();
        }
    }
}
