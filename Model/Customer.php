<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Model;

use Magento\Framework\Model\AbstractModel;
use Pagarme\Pagarme\Api\Data\CustomerInterface;

/**
 * Class SavedCard
 * @package Pagarme\Pagarme\Model
 */
class Customer extends AbstractModel implements CustomerInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Pagarme\Pagarme\Model\ResourceModel\Customer::class);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->getData(self::CODE);
    }

    /**
     * @param string $code
     * @return CustomerInterface
     */
    public function setCode(string $code): CustomerInterface
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return string
     */
    public function getPagarmeId(): string
    {
        return $this->getData(self::PAGARME_ID);
    }

    /**
     * @param string $pagarmeId
     * @return CustomerInterface
     */
    public function setPagarmeId(string $pagarmeId): CustomerInterface
    {
        return $this->setData(self::PAGARME_ID, $pagarmeId);
    }
}
