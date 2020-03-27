<?php

namespace MundiPagg\MundiPagg\Api;

class Repetition extends \Mundipagg\Core\Recurrence\Aggregates\Repetition
{
    /**
     * @return \MundiPagg\MundiPagg\Api\AbstractValidString
     */
    public function getMundipaggId()
    {
        return $this->mundipaggId;
    }
}
