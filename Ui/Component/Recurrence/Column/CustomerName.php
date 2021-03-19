<?php

namespace Pagarme\Pagarme\Ui\Component\Recurrence\Column;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\RepetitionService;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

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

            $pagarmeCustomer =
                $customerRepository->findByPagarmeId($customerId);
            if (!$pagarmeCustomer) {
                continue;
            }

            $magentoCustomerId = $pagarmeCustomer->getCode();

            $objectManager = ObjectManager::getInstance();
            $customer = $objectManager->get(
                'Magento\Customer\Model\Customer')->load($magentoCustomerId
            );
            $item[$fieldName] = $customer->getName();
        }

        return $dataSource;
    }
}
