/**
 * This code should be migrated to core_module
 */

var MundiPaggCore = {
    paymentMethod : []
};

MundiPaggCore.initPaymentMethod = function (methodCode) {

    this.paymentMethod[methodCode] =
        new PaymentMethodController(methodCode);

    this.paymentMethod[methodCode].init();
};

MundiPaggCore.initBin = function (methodCode, obj) {
    this.paymentMethod[methodCode].initBin(obj);
};

MundiPaggCore.validatePaymentMethod = function (methodCode) {
    this.paymentMethod =
        new PaymentMethodController(methodCode);

    this.paymentMethod.init();
    return this.paymentMethod.formValidation();
}

MundiPaggCore.placeOrder = function(platformObject, data, event) {

    var code = platformObject.getCode();
    var model = platformObject.getModel();

    if (code.indexOf('creditcard') >= 0) {

        var formId = '#' + code + '-form';
        this.paymentMethod[model].getCreditCardToken(
            platformObject.getKey(),
            function (response) {
                if (response !== false) {
                    jQuery(formId + " input[name='payment[cc_token]']").val(response.id);
                    platformObject.placeOrder(data, event);
                }
            },
            function (error) {
                console.log(error);
                window.globalMessageList.addErrorMessage({
                    message: $t("Error to generate card token.")
                });

                $("html, body").animate({scrollTop: 0}, 600);
            }
        );
    } else {
        platformObject.placeOrder(data, event);
    }
}