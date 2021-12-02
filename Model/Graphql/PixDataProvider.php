<?php
declare(strict_types=1);

namespace Pagarme\Pagarme\Model\Graphql;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Format input into value expected when setting payment method
 */
class PixDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'pagarme_pix';

    /**
     * Format input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (!empty($args[self::PATH_ADDITIONAL_DATA])) {
            return $args[self::PATH_ADDITIONAL_DATA];
        }

        return [];
    }
}
