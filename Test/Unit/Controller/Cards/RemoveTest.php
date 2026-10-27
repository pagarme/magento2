<?php

namespace Pagarme\Pagarme\Test\Unit\Controller\Cards;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Pagarme\Pagarme\Api\CardsManagementInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;
use Pagarme\Pagarme\Api\SavedCardRepositoryInterface;
use Pagarme\Pagarme\Controller\Cards\Remove;
use Pagarme\Pagarme\Model\Customer\CardOwnership;
use Pagarme\Pagarme\Test\Unit\BaseTest;
use Psr\Log\LoggerInterface;

class RemoveTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    private $cardsManagement;

    private $savedCardRepository;

    private $messageManager;

    private $ownership;

    private $redirect;

    private $remove;

    protected function setUp(): void
    {
        parent::setUp();

        $session = Mockery::mock(Session::class);
        $session->shouldReceive('isLoggedIn')->andReturn(true);

        $request = Mockery::mock(Http::class);
        $request->shouldReceive('getParam')->with('card_id')->andReturn('mp_core_1');

        $this->redirect = Mockery::mock(Redirect::class);
        $redirectFactory = Mockery::mock(RedirectFactory::class);
        $redirectFactory->shouldReceive('create')->andReturn($this->redirect);

        $this->messageManager = Mockery::mock(ManagerInterface::class);

        $this->cardsManagement = Mockery::mock(CardsManagementInterface::class);
        $this->savedCardRepository = Mockery::mock(SavedCardRepositoryInterface::class);
        $this->ownership = Mockery::mock(CardOwnership::class);

        $this->remove = new Remove(
            Mockery::mock(LoggerInterface::class),
            $this->cardsManagement,
            $this->messageManager,
            $session,
            $this->savedCardRepository,
            $redirectFactory,
            $request,
            $this->ownership
        );
    }

    public function testCardOfAnotherCustomerIsNeitherRemovedFromGatewayNorDeleted()
    {
        $card = Mockery::mock(SavedCardInterface::class);
        $card->shouldReceive('getOwnerId')->andReturn('cus_B');
        $this->savedCardRepository->shouldReceive('get')->with('mp_core_1')->andReturn($card);
        $this->ownership->shouldReceive('ownsPagarmeId')->with('cus_B')->andReturn(false);

        $this->cardsManagement->shouldNotReceive('remove');
        $this->savedCardRepository->shouldNotReceive('delete');
        $this->messageManager->shouldReceive('addErrorMessage')->once();
        $this->redirect->shouldReceive('setPath')->with('pagarme/customer/cards')->once()->andReturnSelf();

        $this->remove->execute();
    }

    public function testCardOfLoggedCustomerIsRemovedFromGatewayAndDeleted()
    {
        $card = Mockery::mock(SavedCardInterface::class);
        $card->shouldReceive('getOwnerId')->andReturn('cus_A');
        $this->savedCardRepository->shouldReceive('get')->with('mp_core_1')->andReturn($card);
        $this->ownership->shouldReceive('ownsPagarmeId')->with('cus_A')->andReturn(true);

        $this->cardsManagement->shouldReceive('remove')->with($card)->once();
        $this->savedCardRepository->shouldReceive('delete')->with($card)->once();
        $this->messageManager->shouldReceive('addSuccessMessage')->once();
        $this->redirect->shouldReceive('setPath')->with('pagarme/customer/cards')->once()->andReturnSelf();

        $this->remove->execute();
    }
}
