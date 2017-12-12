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

class Cctype extends \Magento\Payment\Model\Source\Cctype
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
			'SodexoAlimentacao',
			'SodexoCultura',
			'SodexoGift',
			'SodexoPremium',
			'SodexoRefeicao',
			'SodexoCombustivel',
			'VR',
			'Alelo',
			'Banese',
			'Cabal',
        ];
    }
}
