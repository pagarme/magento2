<?php
/**
 * Class Config
 *
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\GooglePay\Config;


use Pagarme\Pagarme\Gateway\Transaction\Base\Config\AbstractConfig;

class Config extends AbstractConfig implements ConfigInterface
{
    /**
     * @return string
     */
    public function getTitle()
    {
        $title = $this->getConfig(static::PATH_TITLE);

        if(empty($title)){
            return __('Pagar.me Google Pay');
        }

        return $title;
    }

    /**
     * Return Google ID
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getConfig(static::MERCHANT_ID);
    }

    /**
     * Return Merchant Name
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getConfig(static::MERCHANT_NAME);
    }
}
