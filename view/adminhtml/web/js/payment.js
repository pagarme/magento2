require([
    "jquery",
    "jquery/ui",
], function ($) {
    'use strict';

    $(document).ready(function(){
        console.log("carregou o payment")
    });


    var MundipaggAdmin = {};
    MundipaggAdmin.placeOrder = function (order) {
        var code = order.paymentMethod;
        var method = code.split("_");

        var submitFunction = order.submit;
        window.MundipaggAdmin[method[1]].placeOrder(submitFunction)
    }

    window.MundipaggAdmin = MundipaggAdmin
});