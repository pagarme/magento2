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
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Pagarme\Pagarme\Gateway\Transaction\Base\Config\ConfigInterface;
use Pagarme\Pagarme\Model\Account;
use Pagarme\Pagarme\Model\Validation\DashSettingsValidation;

/**
 * class Notifications
 * @package Pagarme\Pagarme\Model
 */
class Notifications extends Message
{
    const PAYMENT_DISABLED_MESSAGE = '%1$s payment method is enabled on your store, but disabled on Pagar.me Dash. '
    . 'Please, access the %2$s and enable it to be able to process %1$s payment on your store.';

    /** @var array */
    private $warnings = [];
    private $config;
    /**
     * @var Account
     */
    private $account;

    /**
     * @param ConfigInterface $config
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        Context $context,
        Registry $registry,
        Account $account,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->config = $config;
        $this->account = $account;
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

    private function addMessages()
    {
        $this->addEnvorimentMessages();
        $this->addConfigMessages();
        $this->addHubConfigMessages();
    }

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

    private function addConfigMessages()
    {
        $customerConfigs = $this->config->getPagarmeCustomerConfigs();

        if ($customerConfigs['showVatNumber'] != 1) {
            $this->warnings[] = __(
                "<b>Show VAT Number on Storefront</b> must be defined as <b>'Yes'</b> on "
                . "<b>Stores</b> > <b>Configuration</b> > <b>Customer</b> > <b>Customer Configuration</b> > <b>Create "
                . "New Account Options</b> for Pagar.me module to work on your store."
            );
        }

        if ($customerConfigs['streetLinesNumber'] != 4) {
            $this->warnings[] = __(
                "<b>Number of Lines in a Street Address</b> must be defined as <b>'4'</b> on <b>Stores</b> > "
                . "<b>Configuration</b> > <b>Customer</b> > <b>Customer Configuration</b> > <b>Name and Address "
                . "Options</b> for Pagar.me module to work on your store."
            );
        }
    }

    private function addHubConfigMessages()
    {
        $hubConfigs = $this->account->getDashSettingsErrors();

        if (empty($hubConfigs)){
            return;
        }

        $linkLabel = 'Dash configurations';
        $linkAccount = 'account-config';
        $linkOrder = 'order-config';
        $linkPayment = 'payment-methods';

        $noticesList = [
            DashSettingsValidation::ACCOUNT_DISABLED => 'Your account is disabled on Pagar.me Dash. '
                . 'Please, contact our support team to enable it.',
            DashSettingsValidation::DOMAIN_EMPTY => sprintf(
                'No domain registered on Pagar.me Dash. Please enter your website\'s domain on the %s '
                . 'to be able to process payment in your store.',
                $this->buildLink($linkLabel, $linkAccount),
            ),
            DashSettingsValidation::DOMAIN_INCORRECT => sprintf(
                'The registered domain is different from the URL of your website. Please correct the '
                . 'domain configured on the %s to be able to process payment in your store.',
                $this->buildLink($linkLabel, $linkAccount),
            ),
            DashSettingsValidation::WEBHOOK_INCORRECT => sprintf(
                'The URL for receiving webhook registered in Pagar.me Dash is different from the URL of '
                . 'your website. Please, click the button below to access the Hub and click the Delete > Confirm '
                . 'button. Then return to your store and integrate again.',
                $this->buildLink('', ''),
            ),
            DashSettingsValidation::MULTIPAYMENTS_DISABLED => sprintf(
                'Multipayment option is disabled on Pagar.me Dash. Please, access the %s '
                . 'and enable it to be able to process payment in your store.',
                $this->buildLink($linkLabel, $linkOrder),
            ),
            DashSettingsValidation::MULTIBUYERS_DISABLED => sprintf(
                'Multibuyers option is disabled on Pagar.me Dash. Please, access the %s '
                . 'and enable it to be able to process payment in your store.',
                $this->buildLink($linkLabel, $linkOrder),
            ),
            DashSettingsValidation::PIX_DISABLED => sprintf(
                self::PAYMENT_DISABLED_MESSAGE,
                'Pix',
                $this->buildLink($linkLabel, $linkPayment),
            ),
            DashSettingsValidation::CREDIT_CARD_DISABLED => sprintf(
                self::PAYMENT_DISABLED_MESSAGE,
                'Credit Card',
                $this->buildLink($linkLabel, $linkPayment),
            ),
            DashSettingsValidation::BILLET_DISABLED => sprintf(
                self::PAYMENT_DISABLED_MESSAGE,
                'Billet',
                $this->buildLink($linkLabel, $linkPayment),
            ),
            DashSettingsValidation::VOUCHER_DISABLED => sprintf(
                self::PAYMENT_DISABLED_MESSAGE,
                'Voucher',
                $this->buildLink($linkLabel, $linkPayment),
            )
        ];

        foreach ($hubConfigs as $error) {
            $this->warnings[] = $noticesList[$error];
        }
    }

    private function buildLink($label, $dashPage)
    {
        if (!$this->account->hasMerchantAndAccountIds()) {
            return $label;
        }

        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $this->account->getDashUrl() . "settings/{$dashPage}/",
            $label
        );
    }
}
