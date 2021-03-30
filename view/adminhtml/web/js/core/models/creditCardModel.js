define([
    "jquery",
    "uiComponent",
    "Magento_Ui/js/modal/alert",
    "Pagarme_Pagarme/js/core/checkout/PlatformFormBiding",
    "Pagarme_Pagarme/js/core/checkout/CreditCardToken",
    "Pagarme_Pagarme/js/core/checkout/Listeners",
    "Pagarme_Pagarme/js/core/checkout/CreditCardValidator",
], function (
    $,
    Class,
    alert,
    PlatformFormBiding,
    CreditCardToken,
    Listeners,
    CreditCardValidator
) {

    var CreditCardModel = {
        formObject: {},
        method: "creditcard",
        PlatformFormBiding,
        CreditCardToken,
        Listeners,
        CreditCardValidator,
        errors: []
    };

    CreditCardModel.init = function (code, config) {
        var method = code.split("_")[1];
        var paymentMethodInit = method + "Init";
        this.formObject = this.PlatformFormBiding[paymentMethodInit](false);
        this.publicKey = this.formObject.publicKey.val();

        this.bindPlaceOrder(config.order);
        this.listenPaymentChange();
        this.addListeners(config);

        window.PagarmeAdmin[method[1]] = this;
    };

    CreditCardModel.listenPaymentChange = function () {
        var _self = this;

        jQuery("#order-billing_method_form input.admin__control-radio").on("click", function(e){
            var amount = _self.formObject.inputAmountWithoutTax.val();
            PagarmeAdmin.updateTotals("remove-tax", 0, amount);
        });
    };

    CreditCardModel.bindPlaceOrder = function(order) {
        var submitFunction = order.submit;
        order.submit = this.placeOrder.bind(this, submitFunction, order);
    };

    CreditCardModel.addListeners = function(config) {
        Listeners.addCreditCardNumberListener(this.formObject);
        Listeners.addCreditCardHolderNameListener(this.formObject);
        Listeners.addCreditCardBrandListener(this.formObject, config.installmenUrl);
        Listeners.addCreditCardInstallmentsListener(this.formObject);
    };

    CreditCardModel.placeOrder = function (placeOrderFunction, order) {
        this.placeOrderFunction = placeOrderFunction;
        var _self = this;

        if (
            typeof order.paymentMethod === "undefined" ||
            _self.method !== order.paymentMethod.split("_")[1]
        ) {
            return _self.placeOrderFunction();
        }

        if (!this.validate()) {
            return;
        }

        this.getCreditCardToken(
            function (data) {
                _self.formObject.creditCardToken.val(data.id);
                _self.placeOrderFunction();
            },
            function (error) {
                var errors = error.responseJSON;
                _self.addErrors("Cartão inválido. Por favor, verifique os dados digitados e tente novamente");
            }
        );
    };

    CreditCardModel.validate = function () {

        var isCreditCardValid = CreditCardValidator.validate(this.formObject);

        if (isCreditCardValid) {
            return true;
        }

        return false;
    };

    CreditCardModel.getCreditCardToken = function (success, error) {
        var modelToken = new CreditCardModel.CreditCardToken(this.formObject);
        modelToken.getToken(this.publicKey)
            .done(success)
            .fail(error);
    };

    CreditCardModel.getData = function () {
        var saveThiscard = 0;
        var formObject = this.formObject;

        if (formObject.saveThisCard.prop( "checked" )) {
            saveThiscard = 1;
        }

        var data = this.fillData();
        data.additional_data.cc_buyer_checkbox = false;

        if (
            typeof formObject.multibuyer !== "undefined" &&
            formObject.multibuyer.showMultibuyer.prop( "checked" ) === true
        ) {
            data = this.fillMultibuyerData(data);
        }

        return data;
    };

    CreditCardModel.fillData = function() {
        var formObject = this.formObject;
        var saveThiscard = false;
        return {
            "method": "pagarme_creditcard",
            "additional_data": {
                "cc_type": formObject.creditCardBrand.val(),
                "cc_last_4": this.getLastFourNumbers(),
                "cc_exp_year": formObject.creditCardExpYear.val(),
                "cc_exp_month": formObject.creditCardExpMonth.val(),
                "cc_owner": formObject.creditCardHolderName.val(),
                "cc_savecard": saveThiscard,
                "cc_saved_card": formObject.savedCreditCardSelect.val(),
                "cc_installments": formObject.creditCardInstallments.val(),
                "cc_token_credit_card": formObject.creditCardToken.val(),
                "cc_card_tax_amount" : formObject.creditCardInstallments.find(":selected").attr("interest")
            }
        };
    };

    CreditCardModel.getLastFourNumbers = function() {
        var number = this.formObject.creditCardNumber.val();
        if (typeof number !== "undefined") {
            return number.slice(-4);
        }
        return "";
    };

    return CreditCardModel;
});
