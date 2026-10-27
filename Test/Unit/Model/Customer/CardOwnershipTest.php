<?php

namespace Pagarme\Pagarme\Test\Unit\Model\Customer;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Pagarme\Pagarme\Api\CustomerRepositoryInterface;
use Pagarme\Pagarme\Api\Data\CustomerInterface;
use Pagarme\Pagarme\Model\Customer\CardOwnership;
use Pagarme\Pagarme\Test\Unit\BaseTest;

class CardOwnershipTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    public function testOwnsPagarmeIdWhenSessionHoldsTheSameId()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getPagarmeId')->andReturn('cus_A');

        $ownership = $this->createOwnership($session, Mockery::mock(CustomerRepositoryInterface::class));

        $this->assertTrue($ownership->ownsPagarmeId('cus_A'));
    }

    public function testDoesNotOwnPagarmeIdOfAnotherCustomer()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getPagarmeId')->andReturn('cus_A');

        $ownership = $this->createOwnership($session, Mockery::mock(CustomerRepositoryInterface::class));

        $this->assertFalse($ownership->ownsPagarmeId('cus_B'));
    }

    public function testResolvesPagarmeIdFromRepositoryWhenSessionHasNone()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getPagarmeId')->andReturn(null);
        $session->shouldReceive('getCustomerId')->andReturn('10');

        $customer = Mockery::mock(CustomerInterface::class);
        $customer->shouldReceive('getPagarmeId')->andReturn('cus_A');
        $results = Mockery::mock(SearchResultsInterface::class);
        $results->shouldReceive('getItems')->andReturn([$customer]);
        $repository = Mockery::mock(CustomerRepositoryInterface::class);
        $repository->shouldReceive('getList')->andReturn($results);

        $ownership = $this->createOwnership($session, $repository);

        $this->assertTrue($ownership->ownsPagarmeId('cus_A'));
    }

    public function testOwnsNothingWhenNoPagarmeIdIsKnown()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getPagarmeId')->andReturn(null);
        $session->shouldReceive('getCustomerId')->andReturn('10');

        $results = Mockery::mock(SearchResultsInterface::class);
        $results->shouldReceive('getItems')->andReturn([]);
        $customerRepository = Mockery::mock(CustomerRepositoryInterface::class);
        $customerRepository->shouldReceive('getList')->andReturn($results);

        $ownership = $this->createOwnership($session, $customerRepository);

        $this->assertNull($ownership->getPagarmeId());
        $this->assertFalse($ownership->ownsPagarmeId(''));
    }

    public function testOwnsCustomerIdOfLoggedCustomerOnly()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getCustomerId')->andReturn('10');

        $ownership = $this->createOwnership($session, Mockery::mock(CustomerRepositoryInterface::class));

        $this->assertTrue($ownership->ownsCustomerId(10));
        $this->assertFalse($ownership->ownsCustomerId(11));
    }

    public function testOwnsNoCustomerIdWhenNobodyIsLoggedIn()
    {
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('getCustomerId')->andReturn(null);

        $ownership = $this->createOwnership($session, Mockery::mock(CustomerRepositoryInterface::class));

        $this->assertFalse($ownership->ownsCustomerId(0));
    }

    private function createOwnership(Session $session, CustomerRepositoryInterface $customerRepository)
    {
        $criteria = Mockery::mock(SearchCriteria::class);
        $criteriaBuilder = Mockery::mock(SearchCriteriaBuilder::class);
        $criteriaBuilder->shouldReceive('addFilter')->andReturnSelf();
        $criteriaBuilder->shouldReceive('create')->andReturn($criteria);

        return new CardOwnership($session, $customerRepository, $criteriaBuilder);
    }
}
