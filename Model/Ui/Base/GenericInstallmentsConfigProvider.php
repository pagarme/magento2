<?php

/**
 * Class GenericInstallmentConfigProvider
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model\Ui\Base;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\StoreManagerInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface as BaseConfig;
use Pagarme\Pagarme\Model\Installments\Config\ConfigInterface;

abstract class GenericInstallmentsConfigProvider implements ConfigProviderInterface
{
    const CODE = null;

    /**
     * @var array
     */
    protected $installments = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var BaseConfig
     */
    protected $baseConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storageManager;

    public function __construct(
        Repository            $assetRepo,
        ConfigInterface       $config,
        BaseConfig            $baseConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->assetRepo = $assetRepo;
        $this->baseConfig = $baseConfig;
        $this->storageManager = $storeManager;
        $this->setConfig($config);
    }

    public function getConfig()
    {
        return [
            'payment' => [
                'ccform' => [
                    'base_url' => $this->storageManager->getStore()->getBaseUrl(),
                    'installments' => [
                        'active' => [$this::CODE => $this->_getConfig()->isActive()],
                        'value' => 0,
                    ],
                    'pk_token' => $this->baseConfig->getPublicKey(),
                    'icons' => $this->getBrandImages(),
                ]
            ],
            'is_multi_buyer_enabled' => $this->_getConfig()->getMultiBuyerActive(),
            'region_states' => $this->getRegionStates()
        ];
    }
    
    /**
     * Return all brands and their respective images
     * @return array{height: int, width: int, url: string}
     */
    private function getBrandImages()
    {
        $possibleBrands = ["Visa", "Elo", "Discover", "Diners", "Credz", "Hipercard", "HiperCard", "Mastercard", 
        "Sodexo", "SodexoAlimentacao", "SodexoCombustivel", "SodexoCultura", "SodexoGift", "SodexoPremium", 
        "SodexoRefeicao", "Cabal", "Aura", "Amex", "Alelo", "VR", "Banese", "Ticket"];
        $brands = [];
        foreach ($possibleBrands as $brand) {
            $brands[$brand] = [
                    'height' => 30,
                    'width' => 46,
                    'url' => $this->assetRepo->getUrl("Pagarme_Pagarme::images/cc/".$brand.".png")
                ];
        }
        return $brands;
    }

    /**
     * @return ConfigInterface
     */
    protected function _getConfig()
    {
        return $this->config;
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    protected function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    protected function getRegionStates()
    {
        /** @fixme Get current country * */
        $objectManager = ObjectManager::getInstance();
        $states = $objectManager
            ->create('Magento\Directory\Model\RegionFactory')
            ->create()->getCollection()->addFieldToFilter('country_id', 'BR');

        return $states->getData();
    }
}
