<?php

namespace Pagarme\Pagarme\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use Pagarme\Pagarme\Model\Enum\TdsModeEnum;

/**
 * Class TdsMode
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */
class TdsMode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => TdsModeEnum::OPTIONAL,
                'label' => __('Optional (fallback automático, padrão)'),
            ],
            [
                'value' => TdsModeEnum::MANDATORY,
                'label' => __('Mandatory (rejeita se não elegível)'),
            ],
        ];
    }
}
