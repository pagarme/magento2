define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'MundiPagg_MundiPagg/js/core/checkout/PlatformFormBiding',
    'MundiPagg_MundiPagg/js/core/checkout/PaymentMethodController',
], function ($, Class, alert, PlatformFormBiding, PaymentMethodController) {
    var MundipaggBootstrap = {
        PlatformFormBiding,
        PaymentMethodController
    };

    MundipaggBootstrap.init = function (code, config) {
        var order = window.order;

        var method = code.split("_");
        var paymentMethodInit = method[1] + "Init";
        var FormObject = this.PlatformFormBiding.FormObject[paymentMethodInit](false);

        window.MundipaggAdmin[method[1]] = FormObject;

        //bind submit button

        var submitFunction = order.submit;

        var _self = this;
        order.submit = function() {
            debugger;
           console.log("moises");
           console.log(_self);
            return submitFunction();
        };
    }

    MundipaggBootstrap.submit = function()
    {

    }

    return MundipaggBootstrap;
});