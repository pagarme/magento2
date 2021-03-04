<?php
/**
 * Class CustomerAddress
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Helper;


class CustomerAddress extends AbstractHelper implements CustomerAddressInterface
{
    /**
     * {@inheritdoc}
     */
    public function getStreetAttribute()
    {
        return $this->getConfigValue(static::PATH_STREET_ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberAttribute()
    {
        return $this->getConfigValue(static::PATH_NUMBER_ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function getComplementAttribute()
    {
        return $this->getConfigValue(static::PATH_COMPLEMENT_ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function getDistrictAttribute()
    {
        return $this->getConfigValue(static::PATH_DISTRICT_ATTRIBUTE);
    }
}
