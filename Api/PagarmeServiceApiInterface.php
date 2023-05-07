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

use PagarmeCoreApiLib\PagarmeCoreApiClient;

/**
 * Class PagarmeServiceApiInterface
 * @package Pagarme\Pagarme\Api
 */
interface PagarmeServiceApiInterface
{
    /**
     * @return PagarmeCoreApiClient
     */
    public function get(): PagarmeCoreApiClient;
}
