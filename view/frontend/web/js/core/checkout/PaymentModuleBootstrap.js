/**
 * This code should be migrated to core_module
 */

define([
    'Pagarme_Pagarme/js/core/checkout/PaymentMethodController',
    'Pagarme_Pagarme/js/core/checkout/PlatformPlaceOrder',
    'Magento_Ui/js/model/messageList'
], (PaymentMethodController, PlatformPlaceOrder, messageList) => {
    const PagarmeCore = {
        paymentMethod : []
    };

    PagarmeCore.init = (methodCode, platformConfig) => {
        PagarmeCore.paymentMethod[methodCode] = new PaymentMethodController(methodCode, platformConfig);
        PagarmeCore.paymentMethod[methodCode].init();
    };

    PagarmeCore.initPaymentMethod = (methodCode, platformConfig) => {
        setTimeout(function() {
            PagarmeCore.init(methodCode, platformConfig);
        }, 1000);
    };

    PagarmeCore.initBin = (methodCode, obj) => {
        PagarmeCore.paymentMethod[methodCode].initBin(obj);
    };

    PagarmeCore.validatePaymentMethod = (methodCode) => {
        PagarmeCore.paymentMethod =
            new PaymentMethodController(methodCode);

        PagarmeCore.paymentMethod.init();
        return PagarmeCore.paymentMethod.formValidation();
    };

    PagarmeCore.placeOrder = (platformObject, model) => {
        if (PagarmeCore.paymentMethod[model].model.validate()) {
            try {
                const platformOrderPlace = new PlatformPlaceOrder(
                    platformObject.obj,
                    platformObject.data,
                    platformObject.event
                );
                PagarmeCore.paymentMethod[model].placeOrder(platformOrderPlace);
            } catch (e) {
                console.log(e);
            }
        }

        const errors = PagarmeCore.paymentMethod[model].model.errors;
        if (errors.length > 0) {
            for (let index in errors) {
                messageList.addErrorMessage(errors[index]);
            }
            jQuery("html, body").animate({scrollTop: 0}, 600);
        }
    };

    return PagarmeCore;
});
