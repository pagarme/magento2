<?php

/**
 * Class AbstractHelper
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Helper\Marketplace;

use Magento\Framework\App\ObjectManager;



class WebkulHelper
{
    const WEBKUL_PRODUCT_COLLECTION_FACTORY_PATH =
    'Webkul\\Marketplace\\Model\\ResourceModel\\Product\\CollectionFactory ';
    const WEBKUL_SALEPERPARTNER_COLLECTION_FACTORY_PATH =
    'Webkul\\Marketplace\\Model\\ResourceModel\\Saleperpartner\\CollectionFactory';

    private $productCollectionFactory;
    private $salesPerPartnerCollectionFactory;
    private $objectManager;

    private $enabled = false;

    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
        if (
            !class_exists(self::WEBKUL_PRODUCT_COLLECTION_FACTORY_PATH) ||
            !class_exists(self::WEBKUL_SALEPERPARTNER_COLLECTION_FACTORY_PATH)
        ) {
            return;
        }

        $this->productCollectionFactory = $this->objectManager->get(
            self::WEBKUL_PRODUCT_COLLECTION_FACTORY_PATH
        );
        $this->salesPerPartnerCollectionFactory = $this->objectManager->get(
            self::WEBKUL_SALEPERPARTNER_COLLECTION_FACTORY_PATH
        );
        $this->setEnabled(true);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getSplitDataFromOrder($platformOrder)
    {
        var_dump($platformOrder);
        return true;
    }
}
