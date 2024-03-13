<?php

namespace Pagarme\Pagarme\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Recipients extends AbstractDb
{
    
    protected function _construct()
    {
        $this->_init('pagarme_module_core_recipients', 'id');
    }
}
