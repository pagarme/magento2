<?php

namespace Pagarme\Pagarme\Model\Ui\Pix;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Pagarme\Core\Kernel\ValueObjects\Configuration\PixConfig;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup as MPSetup;
use Pagarme\Pagarme\Helper\Payment\Pix as PixHelper;

final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'pagarme_pix';

    /**
     * @var PixConfig
     */
    private $pixConfig;

    /**
     * @var Repository
     */
    private $assetRepository;

    public function __construct(Repository $assetRepository)
    {
        MPSetup::bootstrap();
        $moduleConfig = MPSetup::getModuleConfiguration();
        $this->assetRepository = $assetRepository;
        if (!empty($moduleConfig->getPixConfig())) {
            $this->pixConfig = $moduleConfig->getPixConfig();
        }
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'active' => $this->pixConfig->isEnabled(),
                    'title' => $this->pixConfig->getTitle(),
                    'logo' => $this->assetRepository->getUrl(PixHelper::LOGO_URL)
                ]
            ]
        ];
    }
}
