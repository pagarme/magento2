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

MundiPaggCore.placeOrder = function(_self, methodCode, data, event) {

    this.paymentMethod.getCreditCardToken(
        "pk_test_mak6gbVi8iEgb4oB",
        function (response) {
            if (response !== false) {
                jQuery("#mundipagg_creditcard-form input[name='payment[cc_token]']").val(response.id);
                _self.placeOrder(data, event);
            }
        }
    );
}