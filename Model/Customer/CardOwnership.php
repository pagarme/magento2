<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Pagarme\Pagarme\Api\CustomerRepositoryInterface;
use Pagarme\Pagarme\Api\Data\CustomerInterface;

/**
 * Answers whether the logged-in customer owns a saved card.
 * Every card-removal action must go through this before touching the gateway.
 */
class CardOwnership
{
    /** @var Session */
    private $customerSession;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Pagar.me id of the logged-in customer, or null when unknown.
     *
     * @return string|null
     */
    public function getPagarmeId(): ?string
    {
        if ($pagarmeId = $this->customerSession->getPagarmeId()) {
            return $pagarmeId;
        }

        $pagarmeCustomer = current(
            $this->customerRepository->getList(
                $this->searchCriteriaBuilder->addFilter(
                    CustomerInterface::CODE,
                    $this->customerSession->getCustomerId()
                )->create()
            )->getItems()
        );

        if ($pagarmeCustomer instanceof CustomerInterface) {
            return $pagarmeCustomer->getPagarmeId();
        }

        return null;
    }

    /**
     * Owner id stored on a saved card (Pagar.me customer id) belongs to the logged-in customer.
     *
     * @param string $ownerPagarmeId
     * @return bool
     */
    public function ownsPagarmeId(string $ownerPagarmeId): bool
    {
        $pagarmeId = $this->getPagarmeId();

        return $pagarmeId !== null && $pagarmeId === $ownerPagarmeId;
    }

    /**
     * Legacy cards store the Magento customer id directly.
     *
     * @param int|string $customerId
     * @return bool
     */
    public function ownsCustomerId($customerId): bool
    {
        $loggedCustomerId = $this->customerSession->getCustomerId();

        return $loggedCustomerId !== null && (string) $loggedCustomerId === (string) $customerId;
    }
}
