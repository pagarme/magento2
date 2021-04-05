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

class SequenceCriteria implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'OnSuccess',
                'label' => __('On Success'),
            ],
            [
                'value' => 'AuthorizeFirst',
                'label' => __('Always')
            ]
        ];
    }
}
