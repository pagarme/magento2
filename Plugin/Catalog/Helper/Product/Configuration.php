<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Plugin\Catalog\Helper\Product;

use Exception;
use Magento\Catalog\Helper\Product\Configuration as ConfigurationOriginal;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Pagarme\Api\Data\RecurrenceSubscriptionRepetitionsInterface;
use Pagarme\Pagarme\Helper\ProductHelper;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class Configuration
{
    /**
     * @var RecurrenceService
     */
    private $recurrenceService;

    /**
     * @var \Pagarme\Core\Kernel\Aggregates\Configuration
     */
    private $pagarmeConfig;

    /**
     * @param RecurrenceService $recurrenceService
     * @throws Exception
     */
    public function __construct(RecurrenceService $recurrenceService)
    {
        Magento2CoreSetup::bootstrap();
        $this->pagarmeConfig = Magento2CoreSetup::getModuleConfiguration();
        $this->recurrenceService = $recurrenceService;
    }

    /**
     * @param ConfigurationOriginal $subject
     * @param callable $proceed
     * @param ItemInterface $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCustomOptions(
        ConfigurationOriginal $subject,
        callable $proceed,
        ItemInterface $item
    ) {
        $result = $proceed($item);

        $product = $item->getProduct();
        $hasNoRecurrence = !$this->pagarmeConfig->isEnabled() ||
            !$this->pagarmeConfig->getRecurrenceConfig()->isEnabled() ||
            empty($this->recurrenceService->getRecurrenceProductByProductId($product->getId()));
        if ($hasNoRecurrence) {
            return $result;
        }

        return array_map(function ($item) use ($product) {
            if (ucfirst(RecurrenceSubscriptionRepetitionsInterface::CYCLES) !== $item['label']) {
                return $item;
            }

            $item['value'] = ProductHelper::applyDiscount($item['value'], $product);
            return $item;
        }, $result);
    }
}
