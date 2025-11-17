<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Plugin\CatalogWidget\Block\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogWidget\Block\Product\ProductsList as BaseProductsList;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Pagarme\Model\PagarmeConfigProvider;

/**
 * Class ProductsList
 * @package Pagarme\Pagarme\Plugin\CatalogWidget\Block\Product
 */
class ProductsList
{
    /** @var LayoutFactory */
    private $_layoutFactory;

    /** @var ProductInterface */
    protected $product = null;

    /**
     * @var bool|BlockInterface
     */
    protected $rendererListBlock;

    /**
     * @var PagarmeConfigProvider
     */
    protected $pagarmeConfigProvider;

    /**
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        PagarmeConfigProvider $pagarmeConfigProvider
    ) {
        $this->_layoutFactory = $layoutFactory;
        $this->pagarmeConfigProvider = $pagarmeConfigProvider;
    }

    /**
     * @param BaseProductsList $subject
     * @param Product $product
     * @param $result
     * @return string
     */
    public function afterGetProductDetailsHtml(
        BaseProductsList $subject,
        $result,
        ProductInterface $product
    ) {
        if ($this->pagarmeConfigProvider->isRecurrenceEnabled()) {
            return $this->getProductRecurrenceHtml($product) . $result;
        }
        return $result;
    }

    /**
     * @param ProductInterface|null $product
     * @return string
     */
    protected function getProductRecurrenceHtml(?ProductInterface $product = null)
    {
        $typeId = $product ? $product->getTypeId() : null;
        $renderer = $this->getRecurrenceRenderer($product, $typeId);
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    /**
     * Get the renderer that will be used to render the recurrence block
     * @param ProductInterface|null $product
     * @param string|null $type
     * @return bool|AbstractBlock
     */
    protected function getRecurrenceRenderer($product, $type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getRecurrenceRendererList($product);
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * @param ProductInterface|null $product
     * @return bool|BlockInterface
     */
    protected function getRecurrenceRendererList($product)
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout LayoutInterface */
            $layout = $this->_layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('pagarme_pagarme_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();
            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.pagarme.recurrence.renderers');
            if ($product) {
                $this->rendererListBlock->setData('product', $product);
            }
        }
        return $this->rendererListBlock;
    }
}
