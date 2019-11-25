<?php

namespace MundiPagg\MundiPagg\Ui\Component\Recurrence\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class RepetitionsColumn extends Column
{
    /**
     * @var LocalizationService
     */
    protected $i18n;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->i18n = new LocalizationService();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item['repetitions'] = $this->getValueFormatted($item);
        }

        return $dataSource;
    }

    public function getValueFormatted($item)
    {
        $id = $item['id'];
        $productSubscriptionService = new ProductSubscriptionService();
        $productSubscription = $productSubscriptionService->findById($id);
        $repetitions = [];
        foreach ($productSubscription->getRepetitions() as $repetition) {
            $value =
                $repetition->getIntervalCount() .
                " " .
                $repetition->getIntervalType();

            if ($repetition->getDiscountValue() > 0) {
                $value .= " - discount: " . $this->getDiscountFormatted($repetition);
            }

            $repetitions[] = $value;
        }

        return implode(' | ', $repetitions);
    }

    /** Copy Paste, should change to a presentation classe maybe */
    protected function getDiscountFormatted(Repetition $repetition)
    {
        $discountValue = $repetition->getDiscountValue();
        $discountType = $repetition->getDiscountType();
        $symbols = $repetition->getDiscountTypeSymbols();
        $flat = DiscountValueObject::DISCOUNT_TYPE_FLAT;

        if ($repetition->getDiscount()->getDiscountType() == $flat) {
            return implode(" ", [
                $symbols[$discountType],
                $discountValue
            ]);
        }

        return implode("", [
            $discountValue,
            $symbols[$discountType]
        ]);
    }
}
