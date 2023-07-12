<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use NumberFormatter;

class NumberFormatHelper extends AbstractHelper
{
    /**
     * @var NumberFormatter
     */
    private $numberFormatter;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->numberFormatter = new NumberFormatter('pt-BR', NumberFormatter::CURRENCY);
    }

    public function formatToLocalCurrency($number)
    {
        return $this->numberFormatter->format($number);
    }

}
