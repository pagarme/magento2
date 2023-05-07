<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Api\Data;

/**
 * Class CustomerInterface
 * @package Pagarme\Pagarme\Api\Data
 */
interface CustomerInterface
{
    /** @var string */
    const ENTITY_ID = 'id';

    /** @var string */
    const CODE = 'code';

    /** @var string */
    const PAGARME_ID = 'pagarme_id';

    /**
     * Get code
     * @return string
     */
    public function getCode(): string;

    /**
     * Set code
     * @param string $code
     * @return CustomerInterface
     */
    public function setCode(string $code): CustomerInterface;

    /**
     * Get pagarme_id
     * @return string
     */
    public function getPagarmeId(): string;

    /**
     * Set pagarme_id
     * @param string $pagarmeId
     * @return CustomerInterface
     */
    public function setPagarmeId(string $pagarmeId): CustomerInterface;
}
