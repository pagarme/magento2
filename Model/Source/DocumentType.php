<?php

namespace Pagarme\Pagarme\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class DocumentType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'individual',
                'label' => __('CPF'),
            ],
            [
                'value' => 'company',
                'label' => __('CNPJ')
            ],
        ];
    }
}
