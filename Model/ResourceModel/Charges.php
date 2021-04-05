<?php


namespace Pagarme\Pagarme\Model\ResourceModel;

class Charges extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('pagarme_module_core_charge', 'id');
    }
}
