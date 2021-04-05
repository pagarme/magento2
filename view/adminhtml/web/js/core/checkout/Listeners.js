define([
    "jquery",
    "uiComponent",
    "Magento_Ui/js/modal/alert",
    "Pagarme_Pagarme/js/core/checkout/PlatformFormBiding",
], function ($, Class, alert, PlatformFormBiding) {

    var Listeners = {};

    Listeners.addCreditCardNumberListener = function (formObject) {
        var listeners = this;

        formObject.creditCardNumber.on("keydown", function () {
            var element = jQuery(this);
            listeners.limitCharacters(element, 19);
        });

        formObject.creditCardNumber.on("keyup", function () {
            var element = jQuery(this);
            listeners.clearLetters(element);
        });
    };

    Listeners.addCreditCardBrandListener = function (formObject, installmenUrl) {
        var _self = this;
        if (typeof formObject.creditCardBrand === "undefined") {
            return;
        }
        var amount = formObject.inputAmountWithoutTax.val();
        window.PagarmeAdmin.updateTotals("remove-tax", 0, amount);

        formObject.creditCardBrand.on("change", function() {
            var value = jQuery(this).val();
            if (value !== "" && value !== "undefined") {
                _self.fillInstallments(formObject, installmenUrl);
            }
        });
    };

    Listeners.addCreditCardInstallmentsListener = function(formObject) {
        var _self = this;

        if (typeof formObject.creditCardInstallments === "undefined") {
            return;
        }

        formObject.creditCardInstallments.on("change", function() {
            var value = jQuery(this).val();
            if (value !== "" && value !== "undefined") {
                var interest = jQuery(this).find(":selected").attr("interest");
                _self.updateAmount(formObject, interest);
            }
        });
    };

    Listeners.addCreditCardHolderNameListener = function(formObject) {
        var listeners = this;
        formObject.creditCardHolderName.on("keyup", function () {
            var element = jQuery(this);
            listeners.clearNumbers(element);
        });
    };

    Listeners.fillInstallments = function (form, installmenUrl) {
        var _self = this;

        if (typeof form.creditCardBrand === "undefined") {
            return;
        }

        var defaulOption = [{
            "id" : 0,
            "interest" : 0,
            "label" : "Carregando...",
            "value": ""
        }];
        var selectedBrand = form.creditCardBrand.val();

        var amount = form.inputAmountWithoutTax.val();
        if (typeof selectedBrand === "undefined" || selectedBrand === "") {
            selectedBrand = "default";
        }

        if (typeof amount === "undefined") {
            amount = 0;
        }

        _self.updateInstallmentSelect(defaulOption, form.creditCardInstallments);
        form.creditCardInstallments.prop("disabled", true);

        var finalInstallmentsUrl =
            installmenUrl + "/" +
            selectedBrand + "/" +
            amount;

        jQuery.ajax({
            url: finalInstallmentsUrl,
            method: "GET",
            cache: true
        }).done(function(data) {
            _self.updateInstallmentSelect(data, form.creditCardInstallments);
            form.creditCardInstallments.prop("disabled", false);
            _self.updateAmount(form,0);
        });
    };

    Listeners.updateAmount = function(formObject, interest){
        var newInterest = parseFloat(interest).toFixed(2);
        var amountWithoutTax = formObject.inputAmountWithoutTax.val();
        var total = parseFloat(amountWithoutTax) + parseFloat(interest);

        total = total.toFixed(2);
        if (newInterest > 0) {
            formObject.inputAmount.val(total);
            window.PagarmeAdmin.updateTotals("add-tax", newInterest, total);
            return;

        }
        total = parseFloat(amountWithoutTax).toFixed(2);
        formObject.inputAmount.val(amountWithoutTax);
        window.PagarmeAdmin.updateTotals("remove-tax", 0, total);
    };

    Listeners.updateInstallmentSelect = function (installmentsObj, element) {
        var content = "";
        for (var i = 0, len = installmentsObj.length; i < len; i++) {
            content +=
                "<option value='" +
                installmentsObj[i].id +
                "' interest='" +
                installmentsObj[i].interest +
                "'>" +
                installmentsObj[i].label +
                "</option>";
        }

        element.html(content);
    };

    Listeners.limitCharacters = function (element, limit) {
        var val = element.val();

        if (val !== "" && val.length > limit) {
            element.val(val.substring(0, limit));
        }
    };

    Listeners.clearLetters = function (element) {
        var val = element.val();
        var newVal = val.replace(/[^0-9]+/g, "");
        element.val(newVal);
    };

    Listeners.clearNumbers = function (element) {
        var val = element.val();
        var newVal = val.replace(/[0-9]+/g, "");
        element.val(newVal);
    };

    return Listeners;
});
