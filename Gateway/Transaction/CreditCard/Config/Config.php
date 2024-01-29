<?php
/**
 * Class Config
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\CreditCard\Config;

use Pagarme\Pagarme\Gateway\Transaction\Base\Config\TdsConfigInterface;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\AbstractConfig;

class Config extends AbstractConfig implements ConfigInterface, TdsConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getActive()
    {
        return (bool) $this->getConfig(static::PATH_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledSavedCards()
    {
        return (bool) $this->getConfig(static::PATH_ENABLED_SAVED_CARDS);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = $this->getConfig(static::PATH_TITLE);

        if(empty($title)){
            return __('Pagar.me Credit Card');
        }

        return $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTdsActive()
    {
        return (bool) $this->getConfig(static::PATH_TDS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderWithTdsRefused()
    {
        return (bool) $this->getConfig(static::PATH_ORDER_WITH_TDS_REFUSED);
    }

    /**
     * @return string
     */
    public function getTdsMinAmount()
    {
        return $this->getConfig(static::PATH_TDS_MIN_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentAction()
    {
        return $this->getConfig(static::PATH_PAYMENT_ACTION);
    }

    /**
     * @return bool
     */
    public function getAntifraudActive()
    {
        return $this->getConfig(static::PATH_ANTIFRAUD_ACTIVE);
    }

    /**
     * @return string
     */
    public function getAntifraudMinAmount()
    {
        return $this->getConfig(static::PATH_ANTIFRAUD_MIN_AMOUNT);
    }

    /**
     * @return string
     */
    public function getSoftDescription()
    {
        return $this->getConfig(static::PATH_SOFT_DESCRIPTION);
    }

}
