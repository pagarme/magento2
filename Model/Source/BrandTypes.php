<?php

namespace Pagarme\Pagarme\Model\Source;

class BrandTypes
{
    const GENERAL_BRANDS = [
        'Visa',
        'Mastercard',
        'Amex',
        'Hipercard',
        'Diners',
        'Elo',
        'Discover',
        'Aura',
        'JCB'
    ];

    const ONLY_GATEWAY_BRANDS = [
        'Credz',
        'Banese',
        'Cabal'
    ];

    public static function getGatewayBrands()
    {
        return array_merge(
            self::GENERAL_BRANDS,
            self::ONLY_GATEWAY_BRANDS
        );
    }

    public static function getPspBrands()
    {
        return self::GENERAL_BRANDS;
    }
}
