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
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;

class CartConflict
{
    const KEY_REPETITION = 1;

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
        //pra funcionar
        return [$productInfo, $requestInfo];

        $optionsList = $productInfo->getOptions();


        foreach ($optionsList as $index0 => $option) {
            if($option->getSku() != 'recurrence'){
                continue;
            }

            $valueList = $option->getValues();

            foreach ($valueList as $index => $value) {
                foreach ($requestInfo['options'] as $index2 => $optionNivel) {
                   if (($value->getData()['option_type_id'] == $optionNivel) && ($value->getData()['option_id'] == $index2)) {
                       $azsxdc = $value->getData();
                    }
                }
            }
        }

        die('develpment');


        if ($this->checkIsProductNormal($productInfo->getId(), $requestInfo)) {
            return [$productInfo, $requestInfo];
        }

        /* @var Repetition $repetitionRequestObject */
        $repetitionRequestObject = $this->repetitionRepository->find(
            $requestInfo['options'][self::KEY_REPETITION]
        );

        /* @var Item[] $itemQuoteList */
        $itemQuoteList = $cart->getQuote()->getAllVisibleItems();
        foreach ($itemQuoteList as $item) {
            /* @var DataObject $buyRequest */
            $buyRequest = $item->getBuyRequest();
            $repetitionId = $buyRequest['options'][self::KEY_REPETITION];

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

        if (!isset($requestInfo['options'][self::KEY_REPETITION])) {
            return true;
        }

        return false;
    }
}
