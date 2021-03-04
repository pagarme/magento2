<?php
namespace Pagarme\Pagarme\Model\Product;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product;

class Recurrence extends AbstractType
{
    public function deleteTypeSpecificData(Product $product)
    {
    }
}
