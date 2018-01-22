<?php
/**
 * Class Config
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\CreditCard\Config;


use MundiPagg\MundiPagg\Gateway\Transaction\Base\Config\AbstractConfig;

class Config extends AbstractConfig implements ConfigInterface
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
