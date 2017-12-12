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

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\Config;


class Config extends AbstractConfig implements ConfigInterface
{
    /**
     * @return string
     */
    public function getSecretKey()
    {
        if ($this->getTestMode()) {
            return $this->getConfig(static::PATH_SECRET_KEY_TEST);
        }
        
        return $this->getConfig(static::PATH_SECRET_KEY);
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        if ($this->getTestMode()) {
            return $this->getConfig(static::PATH_PUBLIC_KEY_TEST);
        }
        
        return $this->getConfig(static::PATH_PUBLIC_KEY);
    }

    /**
     * @return string
     */
    public function getTestMode()
    {
        return $this->getConfig(static::PATH_TEST_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        if ($this->getConfig(static::PATH_TEST_MODE)) {
            return $this->getConfig(static::PATH_SAND_BOX_URL);
        }

        return $this->getConfig(static::PATH_PRODUCTION_URL);
    }

    public function getPaymentAction(){

    }
}
