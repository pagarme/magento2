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
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
    "use strict";

        rendererList.push(
            {
                type: "mundipagg_creditcard",
                component: "MundiPagg_MundiPagg/js/view/payment/creditcard"
            },
            {
                type: "mundipagg_voucher",
                component: "MundiPagg_MundiPagg/js/view/payment/voucher"
            },
            {
                type: "mundipagg_debit",
                component: "MundiPagg_MundiPagg/js/view/payment/debit"
            },
            {
                type: "mundipagg_billet",
                component: "MundiPagg_MundiPagg/js/view/payment/boleto"
            },
            {
                type: "mundipagg_two_creditcard",
                component: "MundiPagg_MundiPagg/js/view/payment/twocreditcards"
            },
            {
                type: "mundipagg_billet_creditcard",
                component: "MundiPagg_MundiPagg/js/view/payment/boletocreditcard"
            }
        );
        return Component.extend({});
    }
);
