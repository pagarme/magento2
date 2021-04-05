/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
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
                type: "pagarme_pix",
                component: "Pagarme_Pagarme/js/view/payment/pix"
            },
            {
                type: "pagarme_creditcard",
                component: "Pagarme_Pagarme/js/view/payment/creditcard"
            },
            {
                type: "pagarme_voucher",
                component: "Pagarme_Pagarme/js/view/payment/voucher"
            },
            {
                type: "pagarme_debit",
                component: "Pagarme_Pagarme/js/view/payment/debit"
            },
            {
                type: "pagarme_billet",
                component: "Pagarme_Pagarme/js/view/payment/boleto"
            },
            {
                type: "pagarme_two_creditcard",
                component: "Pagarme_Pagarme/js/view/payment/twocreditcards"
            },
            {
                type: "pagarme_billet_creditcard",
                component: "Pagarme_Pagarme/js/view/payment/boletocreditcard"
            }
        );
        return Component.extend({});
    }
);
