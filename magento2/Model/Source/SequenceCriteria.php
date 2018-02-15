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
