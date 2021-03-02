<?php


namespace Pagarme\Pagarme\Api\Data;

interface ChargesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Charges list.
     * @return \Pagarme\Pagarme\Api\Data\ChargesInterface[]
     */
    public function getItems();

    /**
     * Set content list.
     * @param \Pagarme\Pagarme\Api\Data\ChargesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
