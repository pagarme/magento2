<?php
/**
 * Plugin for cart product configuration
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Pagarme\Pagarme\Model\Product\ProductPlan\Cart\Configuration\Plugin;

class Plan
{
    /**
     * Decide whether product has been configured for cart or not
     *
     * @param \Magento\Catalog\Model\Product\CartConfiguration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param array $config
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsProductConfigured(
        \Magento\Catalog\Model\Product\CartConfiguration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        $config
    ) {
        if ($product->getTypeId() == \Pagarme\Pagarme\Model\Product\ProductPlan\Plan::TYPE_CODE) {
            return isset($config['super_group']);
        }

        return $proceed($product, $config);
    }
}
