<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Pagarme\Pagarme\Concrete\Magento2PlatformProductDecorator;

class ProductHelper
{
    public function getProductImage($id)
    {
        $_objectManager = ObjectManager::getInstance();
        $product = $_objectManager->create('Magento\Catalog\Model\Product')->load($id);

        $imageHelper = $_objectManager->get(Image::class);

        $store = $_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        if (empty($product->getImage())) {
            $imageUrl = $imageHelper->getDefaultPlaceholderUrl('image');
        }

        return $imageUrl;
    }

    /**
     * @param int[] $productIdList
     * @return array|null
     */
    public function getProductList(array $productIdList)
    {
        $objectManager = ObjectManager::getInstance();

        $productList = [];
        foreach ($productIdList as $productId) {
            $product =
                $objectManager
                    ->create('Magento\Catalog\Model\Product')
                    ->load($productId);

            $platformProduct = new Magento2PlatformProductDecorator($product);
            $productList[] = $product;
        }

        return $productList;
    }
}
