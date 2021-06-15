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

use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Pagarme\Model\Installments\Config\ConfigInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface as BaseConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Store\Model\StoreManagerInterface;

abstract class GenericInstallmentsConfigProvider implements ConfigProviderInterface
{
    const CODE = null;

    protected $installments = [];
    protected $installmentsBuilder;
    protected $installmentsConfig;
    protected $config;
    protected $_assetRepo;
    protected $baseConfig;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        ConfigInterface $config,
        BaseConfig $baseConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->_assetRepo = $assetRepo;
        $this->baseConfig = $baseConfig;
        $this->storageManager = $storeManager;
        $this->setConfig($config);
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'ccform' => [
                    'base_url' => $this->storageManager->getStore()->getBaseUrl(),
                    'installments' => [
                        'active' => [$this::CODE => $this->_getConfig()->isActive()],
                        'value' => 0,
                    ],
                    'pk_token' => $this->baseConfig->getPublicKey(),
                    'icons' => [
                        'Visa' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Visa.png")
                        ],
                        'Elo' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Elo.png")
                        ],
                        'Discover' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Discover.png")
                        ],
                        'Diners' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Diners.png")
                        ],
                        'Credz' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Credz.png")
                        ],
                        'Hipercard' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Hipercard.png")
                        ],
                        'HiperCard' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Hipercard.png")
                        ],
                        'Mastercard' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Mastercard.png")
                        ],
                        'Sodexo' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Sodexo.png")
                        ],
                        'SodexoAlimentacao' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoAlimentacao.png")
                        ],
                        'SodexoCombustivel' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoCombustivel.png")
                        ],
                        'SodexoCultura' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoCultura.png")
                        ],
                        'SodexoGift' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoGift.png")
                        ],
                        'SodexoPremium' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoPremium.png")
                        ],
                        'SodexoRefeicao' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/SodexoRefeicao.png")
                        ],
                        'Cabal' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Cabal.png")
                        ],
                        'Aura' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Aura.png")
                        ],
                        'Amex' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Amex.png")
                        ],
                        'Alelo' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Alelo.png")
                        ],
                        'VR' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/VR.png")
                        ],
                        'Banese' => [
                            'height' => 30,
                            'width' => 46,
                            'url' => $this->_assetRepo->getUrl("Pagarme_Pagarme::images/cc/Banese.png")
                        ],
                    ],
                ]
            ],
            'is_multi_buyer_enabled' => $this->_getConfig()->getMultiBuyerActive(),
            'region_states' => $this->getRegionStates()
        ];

        return $config;
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

    /**
     * @return ConfigInterface
     */
    protected function _getConfig()
    {
        return $this->config;
    }

    protected function getRegionStates()
    {
        /** @fixme Get current country **/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $states = $objectManager
            ->create('Magento\Directory\Model\RegionFactory')
            ->create()->getCollection()->addFieldToFilter('country_id', 'BR');

        return $states->getData();
    }
}
