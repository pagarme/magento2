var FormHandler = function () {
    formObject = {}
};

FormHandler.prototype.init = function (formObject) {
    this.formObject = formObject;
};

FormHandler.prototype.switchBrand = function (brand) {
    var brandsSelector = this.formObject.containerSelector + ' .brands';

    jQuery(brandsSelector).css('filter', 'grayscale(100%)');

    if(typeof brand != 'undefined' && brand.length > 0){
        var brandSelector =
            this.formObject.containerSelector + ' .' +
            brand.toLowerCase();

        jQuery(brandSelector).css('filter', 'none');
        this.formObject.creditCardBrand.val(brand);

        return;
    }

    this.formObject.creditCardBrand.val('');
    this.formObject.creditCardNumber.change();
};

FormHandler.prototype.updateInstallmentSelect = function (installmentsObj, element, installmentSelected = null) {
    var content = "";
    for (var i = 0, len = installmentsObj.length; i < len; i++) {
        content +=
            "<option value='" +
                installmentsObj[i].id +
                "' interest='" +
                installmentsObj[i].interest +
                "' total_with_tax='" +
                installmentsObj[i].total_with_tax +
                "'>" +
                installmentsObj[i].label +
            "</option>";
    }

    element.html(content);

    for (var i = 0; i < element[0].length; i++) {
        var option = element[0].options[i];
        if (option.value == installmentSelected) {
            element.val(installmentSelected);
        }
    }
};

FormHandler.prototype.fillBrandList = function (brandsObject, formObject) {
    var html = '';

    for (var i = 0, len = brandsObject.length; i < len; i++) {
        if (!brandsObject[i]) continue;
        html +=
            "<li class='item'>" +
            "<img src='" + brandsObject[i].image + "' " +
            "alt='" + brandsObject[i].title + "' " +
            "width='46' " +
            "height='30' " +
            "class='brands " +
            brandsObject[i].title.toLowerCase() +
            "'>" +
            "</li>";
    }

    if (html == '') {
        jQuery(formObject.containerSelector).find(".nobrand").show();
    }

    jQuery(formObject.containerSelector + ' .credit-card-types').html(html);
};

FormHandler.prototype.hideInputAmount = function () {
    jQuery(this.formObject.containerSelector).find('.amount').hide();
};

FormHandler.prototype.removeInstallmentsSelect = function () {
    jQuery(this.formObject.containerSelector).find('.installments').remove();
}

FormHandler.prototype.removeSavedCardsSelect = function (form) {
    jQuery(this.formObject.containerSelector).find('.choice').remove();
}

FormHandler.prototype.fillExpirationYearSelect = function (formText, method, value = null) {

    var html = '';
    var years = Object.keys(formText.years[method]);
    var len = years.length;

    for (var i = 0; i < len; i++) {
        html +=
            "<option value='" +
            years[i] +
            "'>" +
            years[i] +
            "</option>"
        ;
    }

    jQuery(this.formObject.creditCardExpYear).html(html);

    if (value != null) {
        this.formObject.creditCardExpYear.val(value);
    }
};

FormHandler.prototype.fillExpirationMonthSelect = function (formText, method, value = null) {

    var html = '';
    var months = formText.months[method];
    var monthKeys = Object.keys(months);
    var len = monthKeys.length;

    for (var i = 0; i < len; i++) {
        html +=
            "<option value='" +
            monthKeys[i] +
            "'>" +
            months[i + 1] +
            "</option>"
        ;
    }

    jQuery(this.formObject.creditCardExpMonth).html(html);

    if (value != null) {
        this.formObject.creditCardExpMonth.val(value);
    }
};

FormHandler.prototype.fillSavedCreditCardsSelect = function (platformConfig, formObject) {
    var html = '';
    var cards = platformConfig.savedAllCards[formObject.savedCardSelectUsed]

    var brands = [];
    platformConfig.avaliableBrands[formObject.savedCardSelectUsed].forEach(function (item) {
        brands.push(item.title);
    })

    if (cards) {
        var cardKeys = Object.keys(cards);
        var len = cardKeys.length;

        for (var i = 0; i < len; i++) {

            var hasBrand = brands.includes(cards[i].brand);

            if (!hasBrand) {
                continue;
            }

            html +=
                "<option value='" +
                    cards[i].id +
                "'" +
                " brand='" + cards[i].brand  + "'" +
                ">" +
                    cards[i].brand + " " +
                    cards[i].first_six_digits +
                    ".xxxxxx. " +
                    cards[i].last_four_numbers +
                "</option>"
            ;
        }
    }

    if (html.length > 0 && formObject.savedCreditCardSelect.val() != "new") {
        jQuery(formObject.containerSelector + ' .new').hide();
        jQuery(formObject.containerSelector).find('.saved').show();

        html += "<option value='new'>Preencher dados</option>";
        jQuery(formObject.savedCreditCardSelect).html(html);
    }
};

FormHandler.prototype.fillMultibuyerStateSelect = function (platformConfig, formObject) {
    var html = "";
    var states = platformConfig.region_states;

    if (states) {
        var stateKeys = Object.keys(states);
        var len = stateKeys.length;

        for (var i = 0; i < len; i++) {

            var name = states[i].name || states[i].default_name;

            html +=
                "<option value='" +
                    states[i].code +

                "'>" +
                    name +
                "</option>"
            ;
        }
    }

    if (html.length > 0) {
        jQuery(formObject.multibuyer.state).html(html);
    }
};

FormHandler.prototype.removeMultibuyerForm = function (formObject) {
    jQuery(formObject.containerSelector + ' .multibuyer').remove();
    jQuery(formObject.containerSelector + ' .show_multibuyer_box').remove();
}

FormHandler.prototype.toggleMultibuyer = function (formObject) {
    if (formObject.multibuyer.showMultibuyer.prop('checked')) {

        if (formObject.saveThisCard !== undefined) {
            formObject.saveThisCard.parent().hide();
        }
        jQuery(formObject.containerSelector + ' .multibuyer').show();
        return;
    }

    if (formObject.saveThisCard !== undefined) {
        formObject.saveThisCard.parent().show();
    }
    jQuery(formObject.containerSelector + ' .multibuyer').hide();
    return;
}
