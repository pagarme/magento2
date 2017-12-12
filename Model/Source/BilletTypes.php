<?php
/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 *
 */

namespace MundiPagg\MundiPagg\Model\Source;

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
