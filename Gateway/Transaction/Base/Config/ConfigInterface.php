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

namespace MundiPagg\MundiPagg\Gateway\Transaction\Base\Config;


interface ConfigInterface
{
    const PATH_PUBLIC_KEY_TEST    = 'mundipagg_mundipagg/global/public_key_test';
    const PATH_SECRET_KEY_TEST    = 'mundipagg_mundipagg/global/secret_key_test';
    const PATH_PUBLIC_KEY         = 'mundipagg_mundipagg/global/public_key';
    const PATH_SECRET_KEY         = 'mundipagg_mundipagg/global/secret_key';
    const PATH_TEST_MODE          = 'mundipagg_mundipagg/global/test_mode';

    /**
     * @return string
     */
    public function getSecretKey();

    /**
     * @return string
     */
    public function getPublicKey();

    /**
     * @return string
     */
    public function getTestMode();

    /**
     * @return string
     */
    public function getBaseUrl();
}
