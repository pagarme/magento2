<?php


namespace Pagarme\Pagarme\Model\ResourceModel\Cards;

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
            'Pagarme\Pagarme\Model\Cards',
            'Pagarme\Pagarme\Model\ResourceModel\Cards'
        );
    }
}
