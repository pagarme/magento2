<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductHelper extends AbstractHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Image
     */
    private $imageHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductImage($id)
    {
        $product = $this->productRepository->getById($id);

        $store = $this->storeManager->getStore();
        $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        if (empty($product->getImage())) {
            $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl('image');
        }

        return $imageUrl;
    }
}
