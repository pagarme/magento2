<?php

namespace Pagarme\Pagarme\Model\Validation;

use Magento\Config\Model\Config as Magento2ModelConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface as ScopeInterface;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
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
        Magento2CoreSetup::bootstrap();
        $objectManager = ObjectManager::getInstance();

        /**
         * @var MutableScopeConfig $storeConfig
         */
        $storeConfig = $objectManager->get(MutableScopeConfig::class);

        /**
         * @var Magento2ModelConfig $config
         */
        $config = $objectManager->get(Magento2ModelConfig::class);

        $storeId = $magento2CoreSetup::getCurrentStoreId();

        $storeConfig->setValue(
            $this->getPath(),
            $this->getValue(),
            ScopeInterface::SCOPE_WEBSITES,
            $storeId
        );

        try {
            $magento2CoreSetup->loadModuleConfigurationFromPlatform($storeConfig);
            $this->verifyScopeAndValue($storeConfig);
        } catch (Exception $e) {
            throw new ValidatorException(__($e->getMessage()));
        }

        parent::beforeSave();
    }

    protected function verifyScopeAndValue($storeConfig)
    {
        $oldValue = $this->getOldValue();
        $newValue = $this->getValue();
        $scope = $this->getScope();

        $allowedScopes = [
            ScopeInterface::SCOPE_WEBSITES,
            'default'
        ];

        if (
            !in_array($scope, $allowedScopes) &&
            $oldValue != $newValue
        ) {
            $i18n = new LocalizationService();
            $comment = $i18n->getDashboard(
                "Pagar.me module should be configured on Websites scope, please change to website scope to apply these changes"
            );

            throw new Exception($comment);
        }
    }
}
