<?php

namespace MundiPagg\MundiPagg\Model\ResourceModel\Subscriptions;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'MundiPagg\MundiPagg\Model\Subscriptions',
            'MundiPagg\MundiPagg\Model\ResourceModel\Subscriptions'
        );
    }

//    protected function _renderFiltersBefore() {
//        $this->getSelect()->where('main_table.status = 1');
//        parent::_renderFiltersBefore();
//    }
}
