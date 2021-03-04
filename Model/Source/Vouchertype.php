<?php

namespace Pagarme\Pagarme\Model\Source;

/**
 * CC Types
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

class Vouchertype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return [
            'VR',
            'Alelo',
            'Sodexo'
        ];
    }
}
