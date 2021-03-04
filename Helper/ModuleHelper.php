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

use Magento\Framework\Module\ModuleListInterface;

class ModuleHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $moduleList;
    protected $taxVat;

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

    public function setTaxVat($taxVat,$format = true)
    {
        $this->taxVat = trim($taxVat);

        if(empty($this->taxVat)){
            $this->taxVat = null;
        }

        if(($format ==  true) and (!is_null($this->taxVat))){
             $this->taxVat = preg_replace("/[^0-9]/", "", $this->taxVat);
        }
        return $this->taxVat;

    }

    public function getTaxVat()
    {
        return $this->taxVat;
    }
}
