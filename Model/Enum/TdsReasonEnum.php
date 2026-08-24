<?php

namespace Pagarme\Pagarme\Model\Enum;

/**
 * Class TdsReasonEnum
 * @package Pagarme\Pagarme\Model\Enum
 */
abstract class TdsReasonEnum
{
    const NOT_ELIGIBLE = 'not_eligible';

    const CONFIG_DISABLED = 'config_disabled';

    const AMOUNT_BELOW_MIN = 'amount_below_min';

    const BRAND_NOT_SUPPORTED = 'brand_not_supported';

    const AUTHORIZED = '3ds_authorized';

    const DECLINED = '3ds_declined';

    const UNKNOWN = 'unknown';
}
