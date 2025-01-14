<?php
/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Model;

use Magento\AdminNotification\Model\System\Message;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Pagarme\Core\Middle\Model\Account as CoreAccount;
use Pagarme\Core\Middle\Model\Account\PaymentMethodSettings;
use Magento\Framework\UrlInterface;
use Pagarme\Pagarme\Block\Adminhtml\System\Config\Form\Field\HubIntegration;
use Pagarme\Pagarme\Concrete\Magento2CoreSetup;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface;
use Pagarme\Pagarme\Model\Account;

/**
 * class Notifications
 * @package Pagarme\Pagarme\Model
 */
class Notifications extends Message
{
    const PAYMENT_DISABLED_MESSAGE = '<b>%1$s</b> payment method is enabled on your store, '
        . 'but disabled on Pagar.me Dash. Please, access the <b>%2$s</b> and enable it to be able to '
        . 'process %1$s payment on your store.';

    /**
     * @var array
     */
    private $warnings = [];
    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var Account
     */
    private $account;
    /**
     * @var HubIntegration
     */
    private $hubIntegration;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param ConfigInterface $config
     * @param Context $context
     * @param Registry $registry
     * @param Account $account
     * @param HubIntegration $hubIntegration
     * @param UrlInterface $urlInterface
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        ConfigInterface   $config,
        Context           $context,
        Registry          $registry,
        Account           $account,
        HubIntegration    $hubIntegration,
        UrlInterface      $urlInterface,
        AbstractResource  $resource = null,
        AbstractDb        $resourceCollection = null,
        array             $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->config = $config;
        $this->account = $account;
        $this->hubIntegration = $hubIntegration;
        $this->urlInterface = $urlInterface;
        $this->addMessages();
    }

    /**
     * @return int
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * @return string
     */
    public function getText(): ?string
    {
        if (empty($this->warnings)) {
            return null;
        }

        $html = '<div class="pagarme-admin-warnings"><h3>Pagar.me</h3>';
        foreach ($this->warnings as $warning) {
            $html .= "<div>{$warning}</div>";
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * @return boolean
     */
    public function isDisplayed(): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return count($this->warnings) > 0;
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    private function addMessages()
    {
        $this->addEnvorimentMessages();
        $this->addConfigMessages();
        $this->addDashSettingsMessages();
    }

    /**
     * @return void
     */
    private function addEnvorimentMessages()
    {
        if (!$this->config->isHubEnabled()) {
            $this->warnings[] = __(
                'Pagar.me module is not yet integrated to the HUB. Complete the integration to start selling!'
            );
        }

        if ($this->config->isSandboxMode()) {
            $this->warnings[] = __(
                'This store is linked to the Pagar.me test environment. This environment is intended for integration'
                . ' validation and does not generate real financial transactions.'
            );
        }
    }

    /**
     * @return void
     */
    private function addConfigMessages()
    {
        $customerConfigs = $this->config->getPagarmeCustomerConfigs();
        $customerUrl = $this->urlInterface->getUrl('adminhtml/system_config/edit/section/customer');

        if ($customerConfigs['showVatNumber'] != 1) {
            $this->warnings[] = sprintf(
                __(
                    '<b>Show VAT Number on Storefront</b> must be defined as <b>&quot;Yes&quot;</b> on <b>Stores</b> > '
                    . '<b>Configuration</b> > <b>Customers</b> > <b>%sCustomer Configuration%s</b> > <b>'
                    . 'Create New Account Options</b> for Pagar.me module to work on your store.'
                ),
                "<a href='{$customerUrl}'>",
                '</a>'
            );
        }

        if ($customerConfigs['streetLinesNumber'] != 4) {
            $this->warnings[] = sprintf(
                __(
                    '<b>Number of Lines in a Street Address</b> must be defined as <b>&quot;4&quot;</b> on '
                    . '<b>Stores</b> > <b>Configuration</b> > <b>Customers</b> > <b>%sCustomer Configuration%s</b> > '
                    . '<b>Name and Address options</b> for Pagar.me module to work on your store.'
                ),
                "<a href='{$customerUrl}'>",
                '</a>'
            );
        }
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    private function addDashSettingsMessages()
    {
        $dashSettings = $this->account->getDashSettingsErrors();

        if (empty($dashSettings)) {
            return;
        }

        $linkLabel = __('Dash configurations');
        $linkAccount = 'account-config';
        $linkOrder = 'order-config';
        $linkPayment = 'payment-methods';

        $noticesList = [
            CoreAccount::ACCOUNT_DISABLED => __('Your account is <b>disabled</b> on Pagar.me Dash. '
                . 'Please, contact our support team to enable it.'),
            CoreAccount::WEBHOOK_INCORRECT => sprintf(
                __('The URL for receiving <b>webhooks</b> registered in Pagar.me Dash is different from the URL of '
                . 'your website. Please, <b>%s</b> to access the Hub and click the Delete > Confirm '
                . 'button. Then return to your store and integrate again.'),
                $this->buildHubLink(__('click here'))
            ),
            CoreAccount::MULTIPAYMENTS_DISABLED => sprintf(
            __('<b>Multipayment</b> option is disabled on Pagar.me Dash. Please, access the <b>%s</b> '
                . 'and enable it to be able to process payment in your store.'),
                $this->buildDashLink($linkLabel, $linkOrder)
            ),
            CoreAccount::MULTIBUYERS_DISABLED => sprintf(
                __('<b>Multibuyers</b> option is disabled on Pagar.me Dash. Please, access the <b>%s</b> '
                . 'and enable it to be able to process payment in your store.'),
                $this->buildDashLink($linkLabel, $linkOrder)
            ),
            PaymentMethodSettings::PIX_DISABLED => sprintf(
                __(self::PAYMENT_DISABLED_MESSAGE),
                'Pix',
                $this->buildDashLink($linkLabel, $linkPayment)
            ),
            PaymentMethodSettings::CREDITCARD_DISABLED => sprintf(
                __(self::PAYMENT_DISABLED_MESSAGE),
                __('Credit Card'),
                $this->buildDashLink($linkLabel, $linkPayment)
            ),
            PaymentMethodSettings::BILLET_DISABLED => sprintf(
                __(self::PAYMENT_DISABLED_MESSAGE),
                __('Billet'),
                $this->buildDashLink($linkLabel, $linkPayment)
            ),
            PaymentMethodSettings::VOUCHER_DISABLED => sprintf(
                __(self::PAYMENT_DISABLED_MESSAGE),
                'Voucher',
                $this->buildDashLink($linkLabel, $linkPayment)
            ),
            PaymentMethodSettings::DEBITCARD_DISABLED => sprintf(
                __(self::PAYMENT_DISABLED_MESSAGE),
                __('Debit Card'),
                $this->buildDashLink($linkLabel, $linkPayment)
            )
        ];

        foreach ($dashSettings as $error) {
            $this->warnings[] = $noticesList[$error];
        }

        $this->addVerifyDashButton();
    }

    /**
     * @param string $label
     * @param string $dashPage
     * @return string
     */
    private function buildDashLink(string $label, string $dashPage = '')
    {
        return $label;
    }

    /**
     * @param string $label
     * @return string
     */
    private function buildHubLink(string $label)
    {
        $installId = Magento2CoreSetup::getModuleConfiguration()->getHubInstallId();
        $hubUrl = $this->hubIntegration->getHubUrl($installId);

        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $hubUrl,
            $label
        );
    }

    /**
     * @return array
     */
    private function addVerifyDashButton()
    {
        $paymentUrl = $this->urlInterface->getUrl('adminhtml/system_config/edit/section/payment');
        return $this->warnings[] = sprintf(
            __(
                'Access %sPayment Methods%s configurations page to clear messages for errors already corrected in '
                . 'the Pagar.me Dash.'
            ),
            "<b><a href='{$paymentUrl}'>",
            '</a></b>'
        );
    }
}
