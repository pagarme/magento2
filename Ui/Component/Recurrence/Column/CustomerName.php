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

class CustomerName extends Column
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

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {

            $customerRepository = new CustomerRepository();
            $customerId = new CustomerId($item['customer_id']);

            $mundipaggCustomer =
                $customerRepository->findByMundipaggId($customerId);
            $magentoCustomerId = $mundipaggCustomer->getCode();

            $objectManager = ObjectManager::getInstance();
            $customer = $objectManager->get(
                'Magento\Customer\Model\Customer')->load($magentoCustomerId
            );
            $item[$fieldName] = $customer->getName();
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
