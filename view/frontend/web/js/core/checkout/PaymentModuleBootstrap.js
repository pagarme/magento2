/**
 * This code should be migrated to core_module
 */

var MundiPaggCore = {
    paymentMethod : []
};

MundiPaggCore.initPaymentMethod = function (methodCode, platformConfig) {
    var _self = this;
    setTimeout(function() {

        _self.paymentMethod[methodCode] =
            new PaymentMethodController(methodCode, platformConfig);
        _self.paymentMethod[methodCode].init();

    }, 1000);
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

    try {
    //This object should be injected on this method, not instantiated here
    var platformOrderPlace = new PlatformPlaceOrder(
        platformObject.obj,
        platformObject.data,
        platformObject.event
    );

        this.paymentMethod[model].placeOrder(platformOrderPlace);
    } catch(e) {
        console.log(e)
    }
    var errors = this.paymentMethod[model].model.errors;
    if (errors.length > 0) {
        for (index in errors) {
            this.messageList.addErrorMessage(errors[index]);
        }
        jQuery("html, body").animate({scrollTop: 0}, 600);
        console.log(errors)
    }
}