<?php

namespace Pagarme\Pagarme\Model\ResourceModel\Recipients;

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
            'Pagarme\Pagarme\Model\Recipient',
            'Pagarme\Pagarme\Model\ResourceModel\Recipients'
        );
    }
}
