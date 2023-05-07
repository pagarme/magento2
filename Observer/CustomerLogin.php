<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Pagarme\Pagarme\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Pagarme\Pagarme\Api\Data\CustomerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CustomerLogin
 * @package Pagarme\Pagarme\Observer
 */
class CustomerLogin implements ObserverInterface
{
    /** @var Session */
    protected $_customerSession;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SearchCriteriaBuilder */
    private $_searchCriteriaBuilder;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     * @param Session $customerSession
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        try {
            $pagarmeCustomer = current(
                $this->customerRepository->getList(
                    $this->_searchCriteriaBuilder->addFilter(
                        CustomerInterface::CODE, $customer->getId()
                    )->create()
                )->getItems()
            );
            if ($pagarmeCustomer && $pagarmeCustomer instanceof CustomerInterface) {
                $this->_customerSession->setData(CustomerInterface::PAGARME_ID, $pagarmeCustomer->getPagarmeId());
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
