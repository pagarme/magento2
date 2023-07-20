<?php

namespace Pagarme\Pagarme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Pricing\Helper\Data;

class HtmlTableHelper extends AbstractHelper
{
    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @param Data $priceHelper
     */
    public function __construct(Data $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param string $text
     * @param string $className
     * @return string
     */
    public function formatTableDataCell($text, $className = '')
    {
        $classAttribute = '';
        if (!empty($className)) {
            $classAttribute = sprintf('class="%s"', $className);
        }
        return sprintf('<td %s>%s</td>', $classAttribute, $text);
    }

    /**
     * @param mixed $number
     * @return string
     */
    public function formatNumberTableDataCell($number)
    {
        return $this->formatTableDataCell($this->formatNumber($number));
    }

    /**
     * @param mixed $number
     * @return float|string
     */
    private function formatNumber($number)
    {
        return $this->priceHelper->currency(($number) / 100);
    }
}
