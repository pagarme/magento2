
/*browser:true*/
/*global define*/
define(
    [
        'MundiPagg_MundiPagg/js/view/payment/default'
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/boleto'
            },

            getCode: function () {
                return 'mundipagg_billet';
            },

            isActive: function () {
                return window.checkoutConfig.payment.mundipagg_billet.active;
            },
            getTitle: function () {
                return window.checkoutConfig.payment.mundipagg_billet.title;
            }
        });
    }
);