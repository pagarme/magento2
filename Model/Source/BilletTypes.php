<?php
/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 *
 */

namespace Pagarme\Pagarme\Model\Source;

class BilletTypes extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @return array
     */
    public function getAllowedTypes()
    {
        return [
            'Itau',
            'Bradesco',
            'Santander',
            'CitiBank',
            'BancoDoBrasil',
            'Caixa',
            'Stone'
        ];
    }
}
