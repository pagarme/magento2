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

use Magento\Framework\Module\ModuleListInterface;

class ModuleHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $moduleList;

    /**
     * ModuleHelper constructor.
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    )
    {
        $this->setModule($moduleList);
    }

    /**
     * @param $moduleName
     * @return mixed
     */
    public function getVersion($moduleName)
    {
        return $this->getModule()->getOne($moduleName)['setup_version'];
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->moduleList;
    }

    /**
     * @param $moduleList
     * @return $this
     */
    public function setModule($moduleList)
    {
        $this->moduleList = $moduleList;

        return $this;
    }

}
