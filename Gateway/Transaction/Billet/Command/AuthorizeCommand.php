<?php
/**
 * Class AuthorizeCommand
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\Command;


use MundiPagg\MundiPagg\Gateway\Transaction\Base\Command\AbstractApiCommand;

use MundiAPILib\Models\CreateOrderRequest;

class AuthorizeCommand extends AbstractApiCommand
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
