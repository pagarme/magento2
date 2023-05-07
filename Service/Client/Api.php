<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Service\Client;

use Pagarme\Pagarme\Api\PagarmeServiceApiInterface;
use Pagarme\Pagarme\Api\SystemInterface;
use Pagarme\Pagarme\Service\Config\Path;
use PagarmeCoreApiLib\PagarmeCoreApiClientFactory;
use PagarmeCoreApiLib\PagarmeCoreApiClient;

/**
 * Class Api
 * @package Pagarme\Pagarme\Service\Client
 */
class Api implements PagarmeServiceApiInterface
{
    /** @var SystemInterface  */
    private SystemInterface $system;

    /** @var PagarmeCoreApiClientFactory */
    private PagarmeCoreApiClientFactory $pagarmeCoreApiClientFactory;

    /**
     * @param PagarmeCoreApiClientFactory $pagarmeCoreApiClient
     * @param SystemInterface $system
     */
    public function __construct(
        PagarmeCoreApiClientFactory $pagarmeCoreApiClient,
        SystemInterface $system
    ) {
        $this->system = $system;
        $this->pagarmeCoreApiClientFactory = $pagarmeCoreApiClient;
    }

    /**
     * @return PagarmeCoreApiClient
     */
    public function get(): PagarmeCoreApiClient
    {
        return $this->pagarmeCoreApiClientFactory->create(
            [
                $this->system->getkey(Path::SECRET_KEY),
                ''
            ]
        );
    }
}
