<?php

namespace Pagarme\Pagarme\Ui\Component\Recurrence\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ProductRecurrenceName extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName] = $this->getValueFormatted($item);
        }

        return $dataSource;
    }

    public function getValueFormatted($item)
    {
        $objectManager = ObjectManager::getInstance();

        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item['product_id']);
        return $product->getName();
    }
}
