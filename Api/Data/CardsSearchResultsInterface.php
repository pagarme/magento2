<?php


namespace Pagarme\Pagarme\Api\Data;

interface CardsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Cards list.
     * @return \Pagarme\Pagarme\Api\Data\CardsInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \Pagarme\Pagarme\Api\Data\CardsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
