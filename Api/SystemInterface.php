<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Api;

use Pagarme\Pagarme\Service\Config\Path;

/**
 * Class SystemInterface
 * @package Pagarme\Pagarme\Api
 */
interface SystemInterface
{
    /**
     * @param $scopeType
     * @param $scopeCode
     * @return bool
     */
    public function getTestMode($scopeType = null, $scopeCode = null): bool;

    /**
     * @param string $type
     * @param $scopeType
     * @param $scopeCode
     * @return string
     */
    public function getKey(string $type = Path::PUBLIC_KEY, $scopeType = null, $scopeCode = null): string;
}
