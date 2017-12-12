<?php
/**
 * Class ConfigInterface
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Gateway\Transaction\Billet\Config;


interface ConfigInterface
{
    const PATH_INSTRUCTIONS     = 'payment/mundipagg_billet/instructions';
    const PATH_TEXT             = 'payment/mundipagg_billet/text';
    const PATH_TYPE_BANK        = 'payment/mundipagg_billet/types';
    const PATH_EXPIRATION_DAYS  = 'payment/mundipagg_billet/expiration_days';

    /**
     * @return string
     */
    public function getInstructions();

    /**
     * @return string
     */
    public function getText();

    /**
     * @return string
     */
    public function getTypeBank();

    /**
     * @return string
     */
    public function getExpirationDays();
}
