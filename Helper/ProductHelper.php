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


    /**
     * @param string $title
     * @return string
     */
    public static function applyDiscount($title, $product)
    {
        $price = ProductHelper::applyMoneyFormat(
            ProductHelper::calculateDiscount(
                ProductHelper::extractValueFromTitle($title),
                ProductHelper::getDiscountAmount($product)
            )
        );

        return strtok($title, '-') . ' - ' . $price;
    }

    /**
     * @param Product $product
     * @return int
     */
    public static function getDiscountAmount($product)
    {
        return abs(number_format($product->getPrice(), 2) - number_format($product->getFinalPrice(), 2));
    }

    /**
     * @param string $title
     * @return float
     */
    public static function extractValueFromTitle($title)
    {
        return (float)preg_replace('/[^0-9,]/', '', $title);
    }

    /**
     * @param int $value
     * @param int $discountAmount
     * @return int
     */
    public static function calculateDiscount($value, $discountAmount)
    {
        return ProductHelper::convertDecimalMoney($value - $discountAmount) > 0 ?
            ProductHelper::convertDecimalMoney($value - $discountAmount) : $value;
    }

    /**
     * @param int $amount
     * @return float
     */
    public static function convertDecimalMoney($amount)
    {
        $amount = number_format($amount, 2, ',', '.');
        return $amount;
    }

    /**
     * @param int $number
     * @return float
     */
    public static function applyMoneyFormat($number)
    {
        $numberFormatter = new \NumberFormatter(
            'pt-BR',
            \NumberFormatter::CURRENCY
        );
        return $numberFormatter->format($number);
    }
}
