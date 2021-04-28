<?php

namespace Pagarme\Pagarme\Ui\Component\Recurrence\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Core\Recurrence\Services\RepetitionService;
use Pagarme\Core\Recurrence\ValueObjects\DiscountValueObject;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Helper\ProductSubscriptionHelper;

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
        Magento2CoreSetup::bootstrap();
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
        $repetitionSevice = new RepetitionService();

        $productSubscription = $productSubscriptionService->findById($id);
        $repetitions = [];

        foreach ($productSubscription->getRepetitions() as $repetition) {
            $repetitions[] = $repetitionSevice->getCycleTitle($repetition);
        }

        return implode(' | ', $repetitions);
    }
}
