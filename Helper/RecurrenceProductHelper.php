<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Recurrence\Repositories\RepetitionRepository;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class RecurrenceProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var RepetitionRepository
     */
    protected $repetitionRepository;
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->repetitionRepository = new RepetitionRepository();
        $this->objectManager = ObjectManager::getInstance();
    }

    public function getRepetitionSelected($item)
    {
        $productOptions = $item->getProduct()
            ->getTypeInstance(true)
            ->getOrderOptions($item->getProduct());

        if (empty($productOptions['options'])) {
            return null;
        }

        /** One subscription product should have only one custom option with the
          * repetitions to be selected
          */
        $option = $productOptions['options'][0];

        $optionValue =
            $this->objectManager
                ->get('Magento\Catalog\Model\Product\Option\Value')
                ->load($option['option_value']);

        $repetitionId = $optionValue->getSortOrder();

        $repetition = $this->repetitionRepository->find($repetitionId);
        if ($repetition) {
            return $repetition;
        }

        return null;
    }
}