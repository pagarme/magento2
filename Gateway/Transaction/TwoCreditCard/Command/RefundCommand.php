<?php
/**
 * Class RefundPartialCommand
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2017 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\TwoCreditCard\Command;

use MundiPagg\MundiPagg\Gateway\Transaction\Base\Command\AbstractApiCommand;

class RefundCommand extends AbstractApiCommand
{
    /**
     * @param $request
     * @return mixed
     */
    protected function sendRequest($request)
    {
        if (!isset($request)) {
            throw new \InvalidArgumentException('MundiPagg Request object should be provided');
        }
        return $request;
    }

}


