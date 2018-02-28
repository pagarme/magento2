<?php
namespace MundiPagg\MundiPagg\Model;

use \Magento\Store\Model\ScopeInterface;

/**
 * Class MundiPaggConfigProvider
 *
 * @package MundiPagg\MundiPagg\Model
 */
class MundiPaggConfigProvider
{
    /**
     * Contains if the module is active or not
     */
    const XML_PATH_SOFTDESCRIPTION = 'payment/mundipagg_creditcard/soft_description';

    /**
     * Contains scope config of Magento
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Contains the configurations
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $config
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Returns the soft_description configuration
     *
     * @return string
     */
    public function getSoftDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SOFTDESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    public function validateSoftDescription()
    {
        $softDescription = $this->getSoftDescription();

        if(strlen($softDescription) > 22){
            $newResult = substr($softDescription, 0, 21);
            $this->config->saveConfig(self::XML_PATH_SOFTDESCRIPTION, $newResult, 'default', 0);

            return false;
        }

        return true;
    }

}