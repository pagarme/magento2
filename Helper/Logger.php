<?php
/**
 * Class Logger
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Helper;

class Logger
{
    /**
     * @param mixed $data
     */
    public function logger($data){

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/mundipagg-' . date('Y-m-d') . '.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}
