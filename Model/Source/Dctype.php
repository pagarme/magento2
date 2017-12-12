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

class Dctype extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return [
            'Simulado',
            'Cielo-Visa',
            'Cielo-Master',
            'Cielo-Elo',
            'Redecard-Visa',
            'Redecard-Master'
        ];
    }
}
