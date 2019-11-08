<?php
namespace MundiPagg\MundiPagg\Concrete;

use Mundipagg\Core\Kernel\Interfaces\PlatformProductInterface;

class Magento2PlatformProductDecorator implements PlatformProductInterface
{
    private $platformProduct;

    public function __construct($platformProduct)
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
}
