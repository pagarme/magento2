<?php

namespace MundiPagg\MundiPagg\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Mundipagg\Core\Kernel\Services\LocalizationService;
use Mundipagg\Core\Recurrence\Aggregates\Repetition;
use MundiPagg\MundiPagg\Concrete\Magento2CoreSetup;

class ProductSubscriptionHelper extends AbstractHelper
{
    /**
     * @var LocalizationService
     */
    protected $i18n;

    public function __construct()
    {
        Magento2CoreSetup::bootstrap();
        $this->i18n = new LocalizationService();
    }

    /**
     * @param Repetition $repetition
     * @return string
     */
    public function tryFindDictionaryEventCustomOptionsProductSubscription(
        Repetition $repetition
    ) {
        $intervalCount = $repetition->getIntervalCount();
        $intervalType = $this->i18n->getDashboard($repetition->getIntervalTypeLabel());

        $intervalLabel = "De {$intervalCount} em {$intervalCount} {$intervalType}";
        return $this->i18n->getDashboard($intervalLabel);
    }
}
