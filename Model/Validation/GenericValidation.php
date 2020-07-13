<?php

namespace MundiPagg\MundiPagg\Model\Validation;

use Magento\Config\Model\Config as Magento2ModelConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Magento\Framework\Exception\ValidatorException;
use Exception;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\App\Config\Value;

class GenericValidation extends \Magento\Framework\App\Config\Value
{
    /**
     * @return Value|void
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $magento2CoreSetup = new Magento2CoreSetup();
        $objectManager = ObjectManager::getInstance();

        /**
         * @var MutableScopeConfig $storeConfig
         */
        $storeConfig = $objectManager->get(MutableScopeConfig::class);

        /**
         * @var Magento2ModelConfig $config
         */
        $config = $objectManager->get(Magento2ModelConfig::class);

        $storeId = $config->getStore();
        if (!$storeId) {
            $storeId = 1;
        }

        $storeConfig->setValue(
            $this->getPath(),
            $this->getValue(),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        try {
            $magento2CoreSetup->loadModuleConfigurationFromPlatform($storeConfig);
        } catch (Exception $e) {
            throw new ValidatorException(__($e->getMessage()));
        }

        parent::beforeSave();
    }
}
