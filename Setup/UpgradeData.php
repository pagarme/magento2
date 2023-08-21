<?php


namespace Pagarme\Pagarme\Setup;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Psr\Log\LoggerInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ProductSubscriptionService
     */
    private $productSubscriptionService;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ProductRepositoryInterface $productRepository, State $state, LoggerInterface $logger)
    {
        Magento2CoreSetup::bootstrap();
        $this->productSubscriptionService = new ProductSubscriptionService();
        $this->productRepository = $productRepository;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.2.5', '<')) {
            $this->updateProductSubscriptionOptionsTitle();
        }

        $setup->endSetup();
    }

    /**
     * @throws LocalizedException
     */
    private function updateProductSubscriptionOptionsTitle()
    {
        if (empty($this->state->getAreaCode())) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
        $productSubscriptions = $this->productSubscriptionService->findAll();
        foreach ($productSubscriptions as $productSubscription) {
            $this->applyTitleWithoutParentheses($productSubscription);
        }
    }


    /**
     * @param ProductSubscription $productSubscription
     * @return void
     */
    private function applyTitleWithoutParentheses($productSubscription)
    {
        $productId = $productSubscription->getProductId();
        try {
            $product = $this->productRepository->getById($productId);
            $productOptions = $product->getOptions();
            foreach ($productOptions as $productOption) {
                $isCyclesOption = $productOption->getTitle() === ucfirst(
                        RecurrenceSubscriptionRepetitionsInterface::CYCLES
                    );
                if ($isCyclesOption) {
                    $productOptionValues = $productOption->getValues();
                    foreach ($productOptionValues as $productOptionValue) {
                        $titleWithoutParentheses = str_replace(
                            ['(', ')'],
                            '',
                            $productOptionValue->getTitle()
                        );
                        $productOptionValue->setTitle($titleWithoutParentheses);
                    }

                    try {
                        $this->productRepository->save($product);
                    } catch (CouldNotSaveException | InputException | StateException $e) {
                        $this->logger->error(
                            __(
                                'Error updating cycles options for product with id %s. Error message: %s',
                                $product->getId(),
                                $e->getMessage()
                            )
                        );
                    }
                    break;
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->error(__('Subscription product with id %s not founded', $productId));
        }
    }
}
