<?php

namespace Pagarme\Pagarme\Model\ResourceModel\ProductsPlan;

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
            'Pagarme\Pagarme\Model\ProductsPlan',
            'Pagarme\Pagarme\Model\ResourceModel\ProductsPlan'
        );
    }
}
