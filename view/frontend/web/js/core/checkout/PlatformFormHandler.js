define(['jquery'], ($) => {
    return class FormHandler {
        constructor() {
            this.formObject = {}
        }
        init(formObject) {
            this.formObject = formObject;
        }
        switchBrand(brand) {
            const brandsSelector = this.formObject.containerSelector + ' .brands';
            const brandElement = this.formObject.creditCardBrand;

            $(brandsSelector).css('filter', 'grayscale(100%) opacity(60%)');

            if(typeof brand != 'undefined' && brand.length > 0){
                const brandSelector =
                    this.formObject.containerSelector + ' .' +
                    brand.toLowerCase();

                $(brandSelector).css('filter', 'none');
                brandElement.val(brand);

                if (brandElement.val() !== 'default' && brandElement.val() !== '') {
                    brandElement.change();
                }

                return;
            }

            brandElement.val('');
        }
        updateInstallmentSelect(installmentsObj, element, installmentSelected = null) {
            let content = "";
            for (let i = 0, len = installmentsObj.length; i < len; i++) {
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

            for (let i = 0; i < element[0].length; i++) {
                const option = element[0].options[i];
                if (option.value == installmentSelected) {
                    element.val(installmentSelected);
                }
            }
        }
        fillBrandList(brandsObject, formObject) {
            let html = '';

            for (let i = 0, len = brandsObject.length; i < len; i++) {
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
                $(formObject.containerSelector).find(".nobrand").show();
            }

            $(formObject.containerSelector + ' .credit-card-types').html(html);
        }
        hideInputAmount() {
            $(this.formObject.containerSelector).find('.amount').hide();
        }
        removeInstallmentsSelect = function () {
            $(this.formObject.containerSelector).find('.installments').remove();
        }
        removeSavedCardsSelect = function () {
            $(this.formObject.containerSelector).find('.choice').remove();
        }
        fillExpirationYearSelect(formText, method, value = null) {

            let html = '';
            const years = Object.keys(formText.years[method]);
            const len = years.length;

            for (let i = 0; i < len; i++) {
                html +=
                    "<option value='" +
                    years[i] +
                    "'>" +
                    years[i] +
                    "</option>"
                ;
            }

            $(this.formObject.creditCardExpYear).html(html);

            if (value != null) {
                this.formObject.creditCardExpYear.val(value);
            }
        }
        fillExpirationMonthSelect(formText, method, value = null) {

            let html = '';
            const months = formText.months[method];
            const monthKeys = Object.keys(months);
            const len = monthKeys.length;

            for (let i = 0; i < len; i++) {
                html +=
                    "<option value='" +
                    monthKeys[i] +
                    "'>" +
                    months[i + 1] +
                    "</option>"
                ;
            }

            $(this.formObject.creditCardExpMonth).html(html);

            if (value != null) {
                this.formObject.creditCardExpMonth.val(value);
            }
        }
        fillSavedCreditCardsSelect = function (platformConfig, formObject) {
            let html = '';
            const cards = platformConfig.savedAllCards[formObject.savedCardSelectUsed];
            let firstOptionValue = null;

            const brands = [];
            platformConfig.avaliableBrands[formObject.savedCardSelectUsed].forEach(function (item) {
                brands.push(item.title);
            })

            if (cards) {
                const cardKeys = Object.keys(cards);
                const len = cardKeys.length;

                for (let i = 0; i < len; i++) {

                    const hasBrand = brands.includes(cards[i].brand);

                    if (!hasBrand) {
                        continue;
                    }

                    if (!firstOptionValue) {
                        firstOptionValue = cards[i].id;
                    }

                    html +=
                        "<option value='" +
                        cards[i].id +
                        "'" +
                        " brand='" + cards[i].brand?.toLowerCase()  + "'" +
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
                $(formObject.containerSelector + ' .new').hide();
                $(formObject.containerSelector).find('.saved').show();

                html += "<option value='new'>Preencher dados</option>";
                $(formObject.savedCreditCardSelect).html(html);
                $(formObject.savedCreditCardSelect).val(firstOptionValue);
            }
        }
        fillMultibuyerStateSelect(platformConfig, formObject) {
            let html = "";
            const states = platformConfig.region_states;

            if (states) {
                const stateKeys = Object.keys(states);
                const len = stateKeys.length;

                for (let i = 0; i < len; i++) {

                    const name = states[i].name || states[i].default_name;

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
                $(formObject.multibuyer.state).html(html);
            }
        }
        removeMultibuyerForm(formObject) {
            $(formObject.containerSelector + ' .multibuyer').remove();
            $(formObject.containerSelector + ' .show_multibuyer_box').remove();
        }
        toggleMultibuyer(formObject) {
            if (formObject.multibuyer.showMultibuyer.prop('checked')) {

                if (formObject.saveThisCard !== undefined) {
                    formObject.saveThisCard.parent().hide();
                }
                $(formObject.containerSelector + ' .multibuyer').show();
                return;
            }

            if (formObject.saveThisCard !== undefined) {
                formObject.saveThisCard.parent().show();
            }
            $(formObject.containerSelector + ' .multibuyer').hide();
            return;
        }
    }
});
