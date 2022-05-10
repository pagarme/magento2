<?php
/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

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
    protected array $warnings = [];

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
        if ($config->isSandboxMode()) {
            $this->warnings[] = '<b> Pagar.me </b><br>' . __('<span style=\'background-color:red; color:white; padding:2px 5px\'>Important!</span> This store is linked to the Pagar.me test environment. This environment is intended for integration validation and does not generate real financial transactions.');
        }
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
            $html .= "<div style='padding-bottom:5px;" . (($i != 0) ? "margin-top:5px;" : "") . (($i < $count - 1) ? "border-bottom:1px solid gray;" : "") . "'>" . $this->warnings[$i] . "</div>";
        }
        return $html;
    }

    /**
     * @return boolean
     */
    public function isDisplayed(): bool
    {
        return count($this->warnings) > 0;
    }
}
