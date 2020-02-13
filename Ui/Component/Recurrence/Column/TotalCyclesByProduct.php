<?php

namespace MundiPagg\MundiPagg\Ui\Component\Recurrence\Column;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mundipagg\Core\Kernel\ValueObjects\Id\CustomerId;
use Mundipagg\Core\Payment\Repositories\CustomerRepository;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use Mundipagg\Core\Recurrence\Repositories\RepetitionRepository;
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;
use MundiPagg\MundiPagg\Helper\RecurrenceProductHelper;

class TotalCyclesByProduct extends Column
{
    private $objectManager;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->objectManager = ObjectManager::getInstance();
        Magento2CoreSetup::bootstrap();
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName] = $this->getTotalCycles($item);
        }

        return $dataSource;
    }

    private function getTotalCycles($item)
    {
        $magentoOrder =
            $this->objectManager
                ->get('Magento\Sales\Model\Order')
                ->loadByIncrementId($item['code']);
        $products = $magentoOrder->getAllItems();

        $cycles = [];

        foreach ($products as $product) {
            $cycles[] = $this->getSelectedCycle($product);
        }

        return $this->returnHighestCycle($cycles);
    }

    private function returnHighestCycle(array $cycles)
    {
        arsort($cycles);
        return array_shift($cycles);
    }

    private function getSelectedCycle($product)
    {
        $repetitionRepository = new RepetitionRepository();
        $options = $product->getProductOptions();

        foreach ($options['options'] as $option) {
            $productOption = $this->objectManager
                ->get('Magento\Catalog\Model\Product\Option')
                ->load($option['option_id']);

            if (
                !empty($productOption) &&
                $productOption->getSku() === "recurrence"
            ) {
                $optionValue = $this->objectManager
                    ->get('Magento\Catalog\Model\Product\Option\Value')
                    ->load($option['option_value']);

                $sortOrder = $optionValue->getSortOrder();
                $selectedRepetition = $repetitionRepository->find($sortOrder);
                return $selectedRepetition->getCycles();
            }
        }
    }
}