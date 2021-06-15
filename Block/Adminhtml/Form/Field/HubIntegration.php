<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;

class HubIntegration extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Exception
     */
    protected function _renderValue(AbstractElement $element)
    {
        Magento2CoreSetup::bootstrap();

        $installId = Magento2CoreSetup::getModuleConfiguration()
            ->getHubInstallId();

        $hubUrl = $this->getHubUrl($installId);
        $buttonText = $this->getButtonText($installId);

        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);

        $html .= sprintf(
            '<p
            id="botao-hub"
            hub-url="%s"
            button-text="%s"></p>',
            $hubUrl,
            $buttonText
        );

        $html .= '</td>';

        return $html;
    }

    private function getButtonText($installId)
    {
        return $installId
            ? __("View Integration") : __("Integrate With Pagar.me");
    }

    private function getHubUrl($installId)
    {
        return $installId
            ? $this->getBaseViewIntegrationUrl($installId->getValue())
            : $this->getBaseIntegrateUrl();
    }

    private function getBaseIntegrateUrl()
    {
        $baseUrl = sprintf(
            'https://hub.pagar.me/apps/%s/authorize',
            $this->getPublicAppKey()
        );

        $params = sprintf(
            '?redirect=%swebsite/%s/&install_token/%s',
            $this->getRedirectUrl(),
            Magento2CoreSetup::getCurrentStoreId(),
            $this->getInstallToken()
        );

        return $baseUrl . $params;
    }

    private function getBaseViewIntegrationUrl($installId)
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/edit/%s',
            $this->getPublicAppKey(),
            $installId
        );
    }

    private function getPublicAppKey()
    {
        return Magento2CoreSetup::getHubAppPublicAppKey();
    }

    private function getRedirectUrl()
    {
        return $this->getUrl('pagarme_pagarme/hub/index');
    }

    private function getInstallToken()
    {
        $installSeed = uniqid();
        $hubIntegrationService = new HubIntegrationService();
        $installToken = $hubIntegrationService
            ->startHubIntegration($installSeed);

        return $installToken->getValue();
    }

}
