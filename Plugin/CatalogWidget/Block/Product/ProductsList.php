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
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;

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
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        LayoutFactory $layoutFactory
    ) {
        $this->_layoutFactory = $layoutFactory;
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
        $this->product = $product;
        return $this->getProductRecurrenceHtml($product) . $result;
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    protected function getProductRecurrenceHtml(ProductInterface $product = null)
    {
        $typeId = $product ? $product->getTypeId() : null;
        $renderer = $this->getRecurrenceRenderer($typeId);
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    /**
     * Get the renderer that will be used to render the recurrence block
     * @param string|null $type
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    protected function getRecurrenceRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getRecurrenceRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function getRecurrenceRendererList()
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout LayoutInterface */
            $layout = $this->_layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('pagarme_pagarme_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();
            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.pagarme.recurrence.renderers');
            if ($this->product) {
                $this->rendererListBlock->setData('product', $this->product);
            }
        }
        return $this->rendererListBlock;
    }
}
