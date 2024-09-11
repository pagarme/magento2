<?php

namespace Pagarme\Pagarme\Block\Adminhtml\System\Config\Form\Field;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Model\Account;

class HubIntegration extends Field
{
    /**
     * @var Account
     */
    private $account;

    protected $scopeConfig;

    public function __construct(
        Account $account,
        ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        $this->account = $account;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws Exception
     */
    protected function _renderValue(AbstractElement $element): string
    {
        Magento2CoreSetup::bootstrap();

        $installId = Magento2CoreSetup::getModuleConfiguration()->getHubInstallId();
        $installIdValue = !empty($installId) ? $installId->getValue() : '';
        $defaultInstallId = $this->scopeConfig->getValue(
            'pagarme_pagarme/hub/install_id',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $defaultInstallId = $defaultInstallId ?? '';

        $html = sprintf(
            '<td class="value" data-pagarme-integration-scope="%s">',
            $installIdValue === $defaultInstallId ? 'default' : 'scope'
        );
        $html .= $this->_getElementHtml($element);

        $hidden = ' hidden';

        $html .= sprintf(
            '<a href="%s" id="pagarme-integrate-button" class="pagarme-integration-button%s">%s</a>',
            $this->getBaseIntegrateUrl(),
            $installId ? $hidden : '',
            __("Integrate With Pagar.me")
        );

        $html .= sprintf(
            '<a href="%s" id="pagarme-view-integration-button" class="pagarme-integration-button%s">%s</a>',
            $this->getHubUrl($installId),
            $installId ? '' : $hidden,
            __("View Integration")
        );

        if ($this->account->hasMerchantAndAccountIds()) {
            $html .= sprintf(
                '<a href="%s" target="_blank" id="pagarme-dash-button" class="pagarme-integration-button%s">%s</a>',
                $this->account->getDashUrl(),
                $installId ? '' : $hidden,
                __('Access Pagar.me Dash')
            );
        }

        $html .= '</td>';

        return $html;
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

        if($this->getRequest()->getParam('website') !== null) {
            $params = sprintf(
                '?redirect=%swebsite/%s/&install_token/%s',
                $this->getRedirectUrl(),
                Magento2CoreSetup::getCurrentStoreId(),
                $this->getInstallToken()
            );
        } else {
            $params = sprintf(
                '?redirect=%s&install_token/%s',
                $this->getRedirectUrl(),
                $this->getInstallToken()
            );
        }

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
