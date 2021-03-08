<?php

namespace Pagarme\Pagarme\Ui\Component\Recurrence\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class Interval extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        Magento2CoreSetup::bootstrap();
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $repetition = new Repetition();
        $repetitionService = new RepetitionService();

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $repetition->setInterval($item['interval_type']);
            $repetition->setIntervalCount($item['interval_count']);

            $label = $repetitionService->getCycleTitle($repetition);
            $item[$fieldName] = $label;
        }

        return $dataSource;
    }
}
