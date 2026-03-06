<?php
/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

// Stub to avoid fatal error when running tests outside the Magento 2 context,
// because registration.php depends on Magento\Framework\Component\ComponentRegistrar
// which is not available in this isolated unit testing environment.
namespace Magento\Framework\Component {
    if (!class_exists('\Magento\Framework\Component\ComponentRegistrar')) {
        class ComponentRegistrar
        {
            const MODULE = 'module';

            public static function register(string $type, string $componentName, string $path): void
            {
                // stub - no-op outside the context of Magento
            }
        }
    }
}

namespace {
    require_once __DIR__ . '/../registration.php';
}
