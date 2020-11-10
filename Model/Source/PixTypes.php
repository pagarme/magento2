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

use Magento\Framework\Option\ArrayInterface;

class PixTypes implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'pagarme',
                'label' => __('Pagar.me'),
            ]
        ];
    }
}
