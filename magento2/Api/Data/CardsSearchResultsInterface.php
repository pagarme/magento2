<?php


namespace MundiPagg\MundiPagg\Api\Data;

interface CardsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Cards list.
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \MundiPagg\MundiPagg\Api\Data\CardsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
