<?php


namespace Pagarme\Pagarme\Model\ResourceModel;

class Cards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('pagarme_pagarme_cards', 'id');
    }
}
