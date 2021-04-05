<?php

namespace Pagarme\Pagarme\Ui\Component\Recurrence\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use NumberFormatter;

class PaymentMethodColumn extends Column
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
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
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
        $paymentMethods = [
            'boleto' => "Boleto",
            'credit_card' => $this->i18n->getDashboard('Credit card')
        ];
        $methodSelecteds = array_keys(
            array_filter(
                array_intersect_key($item, $paymentMethods)
            )
        );

        $result = array_map(
            function($method)  use ($paymentMethods) {
                return $paymentMethods[$method];
            },
            $methodSelecteds
        );

        return implode(" - ", $result);
    }
}
