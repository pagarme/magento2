/**
 * This code should be migrated to core_module
 */

var MundiPaggCore = {
    paymentMethod : null
};

MundiPaggCore.initPaymentMethod = function (methodCode) {

    this.paymentMethod =
        new PaymentMethodController(methodCode);

    this.paymentMethod.init();
};

MundiPaggCore.validatePaymentMethod = function (methodCode) {
    this.paymentMethod =
        new PaymentMethodController(methodCode);

    this.paymentMethod.init();
    return this.paymentMethod.formValidation();
}

MundiPaggCore.placeOrder = function(platformObject, data, event) {

    if (platformObject.getCode().indexOf('creditcard') >= 0) {
        this.paymentMethod.getCreditCardToken(
            platformObject.getKey(),
            function (response) {
                if (response !== false) {
                    jQuery("#mundipagg_creditcard-form input[name='payment[cc_token_credit_card]']").val(response.id);
                    platformObject.placeOrder(data, event);
                }
            }
        );
    } else {
        platformObject.placeOrder(data, event);
    }
}