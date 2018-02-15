<?php


namespace MundiPagg\MundiPagg\Model\ResourceModel;

class Cards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mundipagg_mundipagg_cards', 'id');
    }
}
