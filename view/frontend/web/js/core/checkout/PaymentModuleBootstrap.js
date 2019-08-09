/**
 * This code should be migrated to core_module
 */

var MundiPaggCore = {
    paymentMethod : []
};

MundiPaggCore.initPaymentMethod = function (methodCode, platformConfig) {
    this.paymentMethod[methodCode] =
        new PaymentMethodController(methodCode, platformConfig);
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
};

MundiPaggCore.placeOrder = function(platformObject, model) {

    //This object should be injected on this method, not instantiated here
    var platformOrderPlace = new PlatformPlaceOrder(
        platformObject.obj,
        platformObject.data,
        platformObject.event
    );

    this.paymentMethod[model].placeOrder(platformOrderPlace);
}