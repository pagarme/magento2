<?php
namespace Pagarme\Pagarme\Concrete;

use Magento\Framework\App\ObjectManager;
use Pagarme\Core\Kernel\Interfaces\PlatformProductInterface;

class Magento2PlatformProductDecorator implements PlatformProductInterface
{
    private $platformProduct;

    public function __construct($platformProduct = null)
    {
        $this->platformProduct = $platformProduct;
    }

    public function getId()
    {
        $this->platformProduct->getEntityId();
    }

    public function getName()
    {
        return $this->platformProduct->getName();
    }

    public function getDescription()
    {
        return $this->platformProduct->getMetaDescription();
    }

    public function getType()
    {
        return $this->platformProduct->getTypeId();
    }

    public function getStatus()
    {
        return $this->platformProduct->getStatus();
    }

    public function getImages()
    {
        return $this->platformProduct->getMediaGalleryImages();
    }

    public function getPrice()
    {
        return $this->platformProduct->getPrice();
    }

    public function loadByEntityId($entityId)
    {
        $objectManager = ObjectManager::getInstance();
        $product =
            $objectManager
                ->create('Magento\Catalog\Model\Product')
                ->load($entityId);

        $this->platformProduct = $product;
    }

    public function decreaseStock($quantity)
    {
        $quantityAndStock = $this->platformProduct->getQuantityAndStockStatus();
        $stock = $quantityAndStock['qty'];
        $isInStock = $quantityAndStock['is_in_stock'];

        $newStockQty = $stock - $quantity;

        if ($newStockQty <= 0) {
            $newStockQty = 0;
            $isInStock = false;
        }

        $this->platformProduct->setStockData(['qty' => $newStockQty, 'is_in_stock' => $isInStock]);
        $this->platformProduct->setQuantityAndStockStatus(['qty' => $newStockQty, 'is_in_stock' => $isInStock]);
        $this->platformProduct->save();
     }
}
