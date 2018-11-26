<?php
/**
 * Class ConfigProvider
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Model\Ui\Billet;


use Magento\Checkout\Model\ConfigProviderInterface;
use MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config\ConfigInterface;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'mundipagg_billet';

    protected $billetConfig;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $billetConfig
     */
    public function __construct(
        ConfigInterface $billetConfig
    )
    {
        $this->setBilletConfig($billetConfig);
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE =>[
                    'text' => $this->getBilletConfig()->getText(),
                    'title' => $this->getBilletConfig()->getTitle(),
                ]
            ]
        ];
    }

    /**
     * @return ConfigInterface
     */
    protected function getBilletConfig()
    {
        return $this->billetConfig;
    }

    /**
     * @param ConfigInterface $billetConfig
     * @return $this
     */
    protected function setBilletConfig(ConfigInterface $billetConfig)
    {
        $this->billetConfig = $billetConfig;
        return $this;
    }
}
