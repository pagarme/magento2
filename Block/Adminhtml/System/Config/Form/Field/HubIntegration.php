<?php

namespace Pagarme\Pagarme\Block\Adminhtml\System\Config\Form\Field;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\Account;

class HubIntegration extends Field
{
    /**
     * @var Account
     */
    private $account;

    public function __construct(
        Account $account,
        Context $context,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws Exception
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
            '<a id="pagarme-hub-button" href="%s">%s</a>',
            $hubUrl,
            $buttonText
        );

        if ($this->account->hasMerchantAndAccountIds()) {
            $dashUrl = $this->account->getDashUrl();
            $html .= sprintf(
                '<a id="pagarme-dash-button" href="%s" target="_blank">%s</a>',
                $dashUrl,
                __('Access Pagar.me Dash')
            );
        }

        $html .= '</td>';

        return $html;
    }

    /**
     * @param $installId
     * @return Phrase
     */
    private function getButtonText($installId): Phrase
    {
        return $installId
            ? __("View Integration") : __("Integrate With Pagar.me");
    }

    /**
     * @param $installId
     * @return string
     */
    public function getHubUrl($installId): string
    {
        return $installId
            ? $this->getBaseViewIntegrationUrl($installId->getValue())
            : $this->getBaseIntegrateUrl();
    }

    /**
     * @return string
     */
    private function getBaseIntegrateUrl(): string
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

    /**
     * @param $installId
     * @return string
     */
    private function getBaseViewIntegrationUrl($installId): string
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/edit/%s',
            $this->getPublicAppKey(),
            $installId
        );
    }

    /**
     * @return mixed
     */
    private function getPublicAppKey()
    {
        return Magento2CoreSetup::getHubAppPublicAppKey();
    }

    /**
     * @return string
     */
    private function getRedirectUrl(): string
    {
        return $this->getUrl('pagarme_pagarme/hub/index');
    }

    /**
     * @return string
     */
    private function getInstallToken(): string
    {
        $installSeed = uniqid();
        $hubIntegrationService = new HubIntegrationService();
        $installToken = $hubIntegrationService
            ->startHubIntegration($installSeed);

        return $installToken->getValue();
    }
}
