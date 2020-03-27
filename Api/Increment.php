<?php

namespace MundiPagg\MundiPagg\Api;

use MundiAPILib\Models\CreateIncrementRequest;
use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;

class Increment extends \Mundipagg\Core\Recurrence\Aggregates\Increment
{
    /**
     * @return \MundiPagg\MundiPagg\Api\AbstractValidString
     */
    public function getMundipaggId()
    {
        return $this->mundipaggId;
    }
}
