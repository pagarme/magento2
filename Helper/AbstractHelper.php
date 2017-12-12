<?php
/**
 * Class AbstractHelper
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Helper;


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
