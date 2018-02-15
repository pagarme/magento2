<?php


namespace MundiPagg\MundiPagg\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CardsRepositoryInterface
{


    /**
     * Save Cards
     * @param \MundiPagg\MundiPagg\Api\Data\CardsInterface $cards
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MundiPagg\MundiPagg\Api\Data\CardsInterface $cards
    );

    /**
     * Retrieve Cards
     * @param string $cardsId
     * @return \MundiPagg\MundiPagg\Api\Data\CardsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($cardsId);

    /**
     * Retrieve Cards matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MundiPagg\MundiPagg\Api\Data\CardsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Cards
     * @param \MundiPagg\MundiPagg\Api\Data\CardsInterface $cards
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MundiPagg\MundiPagg\Api\Data\CardsInterface $cards
    );

    /**
     * Delete Cards by ID
     * @param string $cardsId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($cardsId);
}
