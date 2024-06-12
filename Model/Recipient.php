<?php

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Model\AbstractModel;

class Recipient extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Pagarme\Pagarme\Model\ResourceModel\Recipients');
    }
    
}
