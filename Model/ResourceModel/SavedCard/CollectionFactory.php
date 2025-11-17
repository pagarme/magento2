<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model\ResourceModel\SavedCard;

use Magento\Framework\ObjectManagerInterface;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class CollectionFactory
 * @package Pagarme\Pagarme\Model\ResourceModel\SavedCard
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * Object Manager instance
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Instance name to create
     * @var string
     */
    private $instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = Collection::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param string|null $ownerId
     * @return Collection
     */
    public function create(?string $ownerId = null): Collection
    {
        /** @var \Pagarme\Pagarme\Model\ResourceModel\SavedCard\Collection $collection */
        $collection = $this->objectManager->create($this->instanceName);
        if ($ownerId) {
            $collection->addFieldToFilter(SavedCardInterface::OWNER_ID, $ownerId);
        }
        return $collection;
    }
}
