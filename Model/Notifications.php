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

/**
 * class Notifications
 * @package Pagarme\Pagarme\Model
 */
class Notifications extends Message
{

    /** @var array */
    protected $warnings = [];
    protected $config;

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
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->config = $config;
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
        $html = null;
        $count = count($this->warnings);

        for ($i = 0; $i < $count; $i++) {
            $html .= '<div style="padding-bottom: 10px;' . (($i !== 0) ? 'margin-top: 10px;' : '') . (($i < $count - 1) ? 'border-bottom: 1px dashed #d1d1d1' : '') . '">' . (($i === 0) ? "<b style='display:block; margin-bottom:8px'> Pagar.me </b>" : '') . __("<span style='background-color:red; color:white; padding:2px 5px'>Important!</span> ") . $this->warnings[$i] . '</div>';
        }
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
    }

    private function addEnvorimentMessages()
    {
        if (!$this->config->isHubEnabled()) {
            $this->warnings[] = __('Pagar.me module is not yet integrated to the HUB. The module will not work on your store. Integrate now and start your sellings!');
        }

        if ($this->config->isSandboxMode()) {
            $this->warnings[] = __('This store is linked to the Pagar.me test environment. This environment is intended for integration validation and does not generate real financial transactions.');
        }
    }

    private function addConfigMessages()
    {
        $customerConfigs = $this->config->getPagarmeCustomerConfigs();

        if ($customerConfigs['showVatNumber'] != 1) {
            $this->warnings[] = __("<b>Show VAT Number on Storefront</b> must be defined as <b>'Yes'</b> on <b>Stores</b> > <b>Configuration</b> > <b>Customer</b> > <b>Customer Configuration</b> > <b>Create New Account Options</b> for Pagar.me module to work on your store.");
        }

        if ($customerConfigs['streetLinesNumber'] != 4) {
            $this->warnings[] = __("<b>Number of Lines in a Street Address</b> must be defined as <b>'4'</b> on <b>Stores</b> > <b>Configuration</b> > <b>Customer</b> > <b>Customer Configuration</b> > <b>Name and Address Options</b> for Pagar.me module to work on your store.");
        }
    }
}
