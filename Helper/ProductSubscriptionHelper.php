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
        $dictionary = [
            'month' => [
                1 => 'monthly',
                2 => 'bimonthly',
                3 => 'quarterly',
                6 => 'semiannual'
            ],
            'year' => [
                1 => 'yearly',
                2 => 'biennial'
            ],
            'week' => [
                1 => 'weekly'
            ]
        ];

        $intervalType = $repetition->getInterval();
        $intervalCount = $repetition->getIntervalCount();

        if (isset($dictionary[$intervalType][$intervalCount])) {
            return $this->i18n->getDashboard($dictionary[$intervalType][$intervalCount]);
        }

        $intervalType = $this->i18n->getDashboard($repetition->getIntervalTypeLabel());
        return "De {$intervalCount} em {$intervalCount} {$intervalType}";
    }
}
