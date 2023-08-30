<?php

namespace Pagarme\Pagarme\Plugin\Sales\AdminOrder\Product\Quote;

use Pagarme\Pagarme\Model\Product\ProductPlan\Plan;

class Initializer
{
    /**
     * @param \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer $subject
     * @param \Magento\Quote\Model\Quote\Item|string $item
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $config
     *
     * @return \Magento\Quote\Model\Quote\Item|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInit(
        \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer $subject,
        $item,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $config
    ) {
        if (is_string($item) && $product->getTypeId() != Plan::TYPE_CODE) {
            $item = $quote->addProduct(
                $product,
                $config,
                \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE
            );
        }
        return $item;
    }
}
