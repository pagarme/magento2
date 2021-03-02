<?php
/**
 * Class InstallmentManagement
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model;

use Magento\Framework\Api\SimpleBuilderInterface;
use Pagarme\Pagarme\Api\InstallmentsManagementInterface;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class InstallmentsManagement
    extends AbstractInstallmentManagement
    implements InstallmentsManagementInterface
{
    protected $builder;

    /**
     * @param SimpleBuilderInterface $builder
     */
    public function __construct(
        SimpleBuilderInterface $builder
    )
    {
        $this->setBuilder($builder);
        parent::__construct();
        Magento2CoreSetup::bootstrap();
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallments()
    {
        $useDefaultInstallmentsConfig = MPSetup::getModuleConfiguration()->isInstallmentsDefaultConfig();

        if (!$useDefaultInstallmentsConfig) {
            return [];
        }

        return $this->getCoreInstallments(
            null,
            null,
            $this->builder->getSession()->getQuote()->getGrandTotal()
        );

        //@fixme deprecated code

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
}
