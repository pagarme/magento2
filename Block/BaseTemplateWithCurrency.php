<?php

/**
 * Class BaseTemplateWithCurrency
 *
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class BaseTemplateWithCurrency extends Template
{
    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @param Context $context
     * @param Data $priceHelper
     * @param array $data
     */
    public function __construct(Context $context, Data $priceHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param mixed $number
     * @return float|string
     */
    public function formatToCurrency($number)
    {
        return $this->priceHelper->currency(($number) / 100);
    }
}
