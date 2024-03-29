<?php

namespace Pagarme\Pagarme\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Recipient extends AbstractModel implements IdentityInterface
{
    protected function _construct()
    {
        $this->_init('Pagarme\Pagarme\Model\ResourceModel\Recipients');
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return $this->getId();
    }
}
