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