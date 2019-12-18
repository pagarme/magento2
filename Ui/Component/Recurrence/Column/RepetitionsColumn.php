<?php

namespace MundiPagg\MundiPagg\Ui\Component\Recurrence\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Kernel\Services\MoneyService;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\ProductSubscriptionService;
use Mundipagg\Core\Recurrence\ValueObjects\DiscountValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\ProductSubscriptionHelper;

class RepetitionsColumn extends Column
{
    /**
     * @var LocalizationService
     */
    protected $i18n;

    /**
     * @var ProductSubscriptionHelper
     */
    protected $productSubscriptionHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->productSubscriptionHelper = new ProductSubscriptionHelper();
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
            $value = $this->productSubscriptionHelper
                ->tryFindDictionaryEventCustomOptionsProductSubscription($repetition);

            if ($repetition->getRecurrencePrice() > 0) {
                $totalAmount = $this->moneyService->centsToFloat(
                    $repetition->getRecurrencePrice()
                );

                $value .= " - (Total: R$ {$totalAmount})";
            }

            $repetitions[] = $value;
        }

        return implode(' | ', $repetitions);
    }
}
