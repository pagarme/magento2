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
