<?php
/**
 * Class RefundPartialCommand
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Gateway\Transaction\CreditCard\Command;

use Pagarme\Pagarme\Gateway\Transaction\Base\Command\AbstractApiCommand;

class RefundCommand extends AbstractApiCommand
{
    /**
     * @param $request
     * @return mixed
     */
    protected function sendRequest($request)
    {
        if (!isset($request)) {
            throw new \InvalidArgumentException('Pagar.me Request object should be provided');
        }
        return $request;
    }

}


