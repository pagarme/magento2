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

namespace Pagarme\Pagarme\Gateway\Transaction\DebitCard\Config;


use Pagarme\Pagarme\Gateway\Transaction\Base\Config\AbstractConfig;

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
    public function getPaymentAction()
    {
        return $this->getConfig(static::PATH_PAYMENT_ACTION);
    }
}
