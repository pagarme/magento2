/**
 * This code should be migrated to core_module
 */

var PagarmeCore = {
    paymentMethod : []
};

PagarmeCore.initPaymentMethod = function (methodCode, platformConfig) {
    var _self = this;
    setTimeout(function() {
        _self.init(methodCode, platformConfig);
    }, 1000);
};

PagarmeCore.init = function (methodCode, platformConfig) {
    this.paymentMethod[methodCode] = new PaymentMethodController(methodCode, platformConfig);
    this.paymentMethod[methodCode].init();
}
PagarmeCore.initBin = function (methodCode, obj) {
    this.paymentMethod[methodCode].initBin(obj);
};

PagarmeCore.validatePaymentMethod = function (methodCode) {
    this.paymentMethod =
        new PaymentMethodController(methodCode);

    this.paymentMethod.init();
    return this.paymentMethod.formValidation();
};

PagarmeCore.placeOrder = function(platformObject, model) {

    if (this.paymentMethod[model].model.validate()) {
        try {
            //This object should be injected on this method, not instantiated here
            var platformOrderPlace = new PlatformPlaceOrder(
                platformObject.obj,
                platformObject.data,
                platformObject.event
            );

            this.paymentMethod[model].placeOrder(platformOrderPlace);
        } catch (e) {
            console.log(e)
        }
    }

    var errors = this.paymentMethod[model].model.errors;
    if (errors.length > 0) {
        for (index in errors) {
            this.messageList.addErrorMessage(errors[index]);
        }
        jQuery("html, body").animate({scrollTop: 0}, 600);
        console.log(errors);
    }
}