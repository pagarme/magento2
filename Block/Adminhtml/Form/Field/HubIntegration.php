<?php

namespace Pagarme\Pagarme\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
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

        $installId = Magento2CoreSetup::getModuleConfiguration()->getHubInstallId();

        $hubUrl = $this->getHubUrl($installId);
        $buttonText = __($this->getButtonText($installId));
        $newWindow = $this->mustOpenInNewWindow($installId);

        $html = '<td class="value">';
        $html .= $this->_getElementHtml($element);
        $html .= sprintf('
        <p
        id="botao-hub"
        hub-url="%s"
        button-text="%s"
        new-window="%s"></p>', $hubUrl, $buttonText, $newWindow);
        $html .= '</td>';

        return $html;
    }

    private function getButtonText($installId)
    {
        return $installId ? "View Integration" : "Integrate With Mundipagg";
    }

    private function getHubUrl($installId)
    {
        return $installId ? $this->getBaseViewUrl($installId->getValue()) : $this->getBaseIntegrateUrl();
    }

    private function mustOpenInNewWindow($installId)
    {
        return false; //!empty($installId);
    }

    private function getBaseIntegrateUrl()
    {
        return "https://stghub.mundipagg.com/apps/{$this->getPublicAppKey()}/authorize?redirect={$this->getRedirectUrl()}&install_token/{$this->getInstallToken()}";
    }

    private function getBaseViewUrl($installId)
    {
        return "https://stghub.mundipagg.com/apps/{$this->getPublicAppKey()}/edit/{$installId}";
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
        $installToken = $hubIntegrationService->startHubIntegration($installSeed);

        return $installToken->getValue();
    }
}
