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
use Mundipagg\Core\Recurrence\Services\RepetitionService;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

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

        foreach ($products as $product) {
            $cycle = $this->getProductCycle($product);
        }
    }

    private function getProductCycle($product)
    {
        $option0 = $product->getProductOption();
        $option = $product->getProductOptions();
        $options = $option['options'][0];
        $optionTypeId = $options['option_id'];
        //$optionId Pegar o option id através do option type id na tabela option type value

        return $option;
    }

}
