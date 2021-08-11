<?php

namespace Pagarme\Pagarme\Model\Source\Marketplace;

use Magento\Framework\Option\ArrayInterface;

class Recipient implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'marketplace_sellers',
                'label' => __('Marketplace and Sellers'),
            ],
            [
                'value' => 'marketplace',
                'label' => __('Marketplace')
            ],
            [
                'value' => 'sellers',
                'label' => __('Sellers')
            ]
        ];
    }
}
