<?php
/**
 * Class AbstractHelper
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Helper;


use Magento\Framework\App\Config\ScopeConfigInterface;

abstract class AbstractHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @param $path
     * @param string $scopeType
     * @param null $scopeCode
     * @return mixed
     */
    protected function getConfigValue($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }
}
