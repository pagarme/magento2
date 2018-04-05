/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder'
    ],
    function (
        $,
        storage,
        urlBuilder
    ) {
        'use strict';

        return function (dataJson) {
            var serviceUrl = 'https://api.mundipagg.com/core/v1/tokens?appId=' + window.checkoutConfig.payment.ccform.pk_token;

            return $.ajax({
                method: "POST",
                url: serviceUrl,
                cache: false,
                data: dataJson
            });
        };
    }
);
