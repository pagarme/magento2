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
use MundiPagg\MundiPagg\Api\InstallmentsManagementInterface;

class InstallmentsManagement implements InstallmentsManagementInterface
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
    }

    /**
     * {@inheritdoc}
     */
    public function getInstallments()
    {
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
