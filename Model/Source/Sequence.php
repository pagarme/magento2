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

class Sequence implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'AnalyseFirst',
                'label' => __('Analyse First'),
            ],
            [
                'value' => 'AuthorizeFirst',
                'label' => __('Authorize First')
            ]
        ];
    }
}
