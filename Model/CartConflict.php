<?php

namespace MundiPagg\MundiPagg\Model;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Mundipagg\Core\Recurrence\Aggregates\ProductSubscription;
use Mundipagg\Core\Recurrence\Repositories\RepetitionRepository;
use Mundipagg\Core\Recurrence\Services\RecurrenceService;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Magento\Catalog\Model\Product\Interceptor;
use Magento\Framework\DataObject;

class CartConflict
{
    const KEY_REPETITION = 141;

    /**
     * @var RepetitionRepository
     */
    private $repetitionRepository;

    /**
     * @var RecurrenceService
     */
    private $recurrenceService;

    /**
     * CartConflict constructor.
     */
    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->repetitionRepository = new RepetitionRepository();
        $this->recurrenceService = new RecurrenceService();
    }

    public function beforeAddProduct(
        Cart $cart,
        Interceptor $productInfo,
        array $requestInfo = null
    ) {
        if ($this->checkIsProductNormal($productInfo->getId(), $requestInfo)) {
            return [$productInfo, $requestInfo];
        }

        /* @var Repetition $repetitionRequestObject */
        $repetitionRequestObject = $this->repetitionRepository->find(
            $requestInfo['super_attribute'][self::KEY_REPETITION]
        );

        /* @var Item[] $itemQuoteList */
        $itemQuoteList = $cart->getQuote()->getAllVisibleItems();
        foreach ($itemQuoteList as $item) {
            /* @var DataObject $buyRequest */
            $buyRequest = $item->getBuyRequest();
            $repetitionId = $buyRequest['super_attribute'][self::KEY_REPETITION];

            /* @var Repetition $repetition */
            $repetitionObject = $this->repetitionRepository->find($repetitionId);
            if (!$repetitionObject->checkRepetitionIsEquals($repetitionRequestObject)) {
                throw new LocalizedException(__("Intervalos diferentes"));
            }
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param int $productId
     * @param $requestInfo
     * @return bool
     */
    private function checkIsProductNormal($productId, $requestInfo)
    {
        /* @var ProductSubscription $recurrenceObject */
        $recurrenceObject = $this->recurrenceService->getRecurrenceProductByProductId(
            $productId
        );

        if (is_null($recurrenceObject)) {
            return true;
        }

        if (!isset($requestInfo['super_attribute'][self::KEY_REPETITION])) {
            return true;
        }

        return false;
    }
}
