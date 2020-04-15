<?php

namespace MundiPagg\MundiPagg\Model\Source;

/**
 * CC Types
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 */

class Debittype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return [
            'Visa',
            'Mastercard',
            'Amex',
            'Hipercard',
            'Diners',
            'Elo',
            'Discover',
            'Aura',
            'JCB',
            'Credz',
            'Banese',
            'Cabal'
        ];
    }
}
