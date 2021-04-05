<?php
/**
 * Class InstallmentManagement
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Api\SimpleBuilderInterface;
use Pagarme\Pagarme\Api\InstallmentsManagementInterface;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

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
