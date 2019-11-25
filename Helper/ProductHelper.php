<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Catalog\Helper\Image;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;

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
}