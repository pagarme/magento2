<?php
/**
 * Class InstallmentsByBrandManagements
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model;

use Magento\Framework\Api\SimpleBuilderInterface;
use MundiPagg\MundiPagg\Api\InstallmentsByBrandManagementInterface;
use Magento\Checkout\Model\Session;
use MundiPagg\MundiPagg\Model\Installments\Config\ConfigByBrand as Config;

class InstallmentsByBrandManagement implements InstallmentsByBrandManagementInterface
{
    protected $builder;
    protected $session;
    protected $cardBrand;

    /**
     * @param SimpleBuilderInterface $builder
     */
    public function __construct(
        SimpleBuilderInterface $builder,
        Session $session,
        Config $config
    )
    {
        $this->setBuilder($builder);
        $this->setSession($session);
        $this->setConfig($config);
    }

    /**
     * @param mixed $brand
     * @return mixed
     */
    public function getInstallmentsByBrand($brand = null)
    {

        $cardBrand = $this->formatCardBrand($brand);
        $this->session->setCardBrand($cardBrand);
        $this->getBuilder()->create();

            $result = [];

        /** @var Installment $item */
        foreach ($this->getBuilder()->getData() as $item) {
            $result[] = [
                'id' => $item->getQty(),
                'interest' => $item->getInterest(),
                'label' => $item->getLabel()
            ];
        }

        return $result;
    }

    /**
     * @param $brand
     * @return string
     */
    protected function formatCardBrand($brand){

        $cardBrand = '_' . strtolower($brand);

        return $cardBrand;

    }

    /**
     * @param SimpleBuilderInterface $builder
     * @return $this
     */
    protected function setBuilder(SimpleBuilderInterface $builder)
    {
        $this->builder = $builder;
        return $this;
    }

    /**
     * @return SimpleBuilderInterface
     */
    protected function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     * @return $this
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return $this
     */
    protected function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

}