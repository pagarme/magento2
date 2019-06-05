
/*browser:true*/
/*global define*/
define(
    [
        'MundiPagg_MundiPagg/js/view/payment/default'
    ],
    function (Component, $t) {

        return Component.extend({
            defaults: {
                template: 'MundiPagg_MundiPagg/payment/creditcard'
            },

            getCode: function () {
                return 'mundipagg_creditcard';
            }
        });
    }
);