<?php


namespace MundiPagg\MundiPagg\Api\Data;

interface ChargesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Charges list.
     * @return \MundiPagg\MundiPagg\Api\Data\ChargesInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \MundiPagg\MundiPagg\Api\Data\ChargesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
