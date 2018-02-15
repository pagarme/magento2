<?php


namespace MundiPagg\MundiPagg\Model\ResourceModel\Charges;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MundiPagg\MundiPagg\Model\Charges',
            'MundiPagg\MundiPagg\Model\ResourceModel\Charges'
        );
    }
}
