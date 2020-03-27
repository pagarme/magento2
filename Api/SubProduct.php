<?php

namespace MundiPagg\MundiPagg\Api;

use \Mundipagg\Mundipagg\Api\Increment;

class SubProduct extends \Mundipagg\Core\Recurrence\Aggregates\SubProduct
{
    /**
     * @return \MundiPagg\MundiPagg\Api\Increment
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @return \MundiPagg\MundiPagg\Api\PricingSchemeValueObject
     */
    public function getPricingScheme()
    {
        return $this->pricingScheme;
    }

    /**
     * @return \MundiPagg\MundiPagg\Api\Repetition
     */
    public function getSelectedRepetition()
    {
        return $this->selectedRepetition;
    }

    /**
     * @return \MundiPagg\MundiPagg\Api\AbstractValidString
     */
    public function getMundipaggId()
    {
        return $this->mundipaggId;
    }

}