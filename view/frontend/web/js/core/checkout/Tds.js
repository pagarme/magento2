define([
    "Magento_Checkout/js/model/url-builder",
    "mage/url",
    'Magento_Checkout/js/model/quote'
], (
    urlBuilder,
    mageUrl,
    quote
) => {
    return class Tds {
        constructor(formObject) {
            this.formObject = formObject;
        }

        getToken() {
            const url = urlBuilder.createUrl("/pagarme/tdstoken", {});
            return jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: mageUrl.build(url),
                async: false,
                cache: false,
            });
        }

        callTdsFunction(tdsToken, tdsData, callbackTds) {
            const challengeWindowSize = '03';
            Script3ds.init3ds(tdsToken, tdsData, callbackTds, challengeWindowSize);
        }

        showErrors(errors, parentObject) {
            if(errors.error?.email) {
                parentObject.addErrors("Ocorreu um problema ao encontrar o e-mail.");
            }
            if(errors.error?.bill_addr) {
                parentObject.addErrors("Ocorreu um problema ao encontrar os endereços.");
            }
            if(errors.error?.card_expiry_date) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de expiração do cartão.");
            }
            if(errors.error?.purchase) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de compra.");
            }
        }

        addTdsAttributeData() {
            const cardForm = this.formObject;
            jQuery(cardForm.containerSelector).attr("data-pagarmecheckout-form", "");
            cardForm.creditCardHolderName.attr("data-pagarmecheckout-element", "holder_name");
            cardForm.creditCardNumber.attr("data-pagarmecheckout-element", "number");
            cardForm.creditCardBrand.attr("data-pagarmecheckout-element", "brand");
            cardForm.creditCardExpMonth.attr("data-pagarmecheckout-element", "exp_month");
            cardForm.creditCardExpYear.attr("data-pagarmecheckout-element", "exp_year");
            cardForm.creditCardCvv.attr("data-pagarmecheckout-element", "cvv");
        }

        removeTdsAttributeData() {
            const cardForm = this.formObject;
            jQuery(cardForm.containerSelector).removeAttr("data-pagarmecheckout-form");
            cardForm.creditCardHolderName.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardNumber.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardBrand.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardExpMonth.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardExpYear.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardCvv.removeAttr("data-pagarmecheckout-element");
        }

        getTdsData(acctType, cardExpiryDate) {
            const billingAddress = quote.billingAddress();
            const  amountInCents = quote.totals().base_grand_total * 100;
            const [
                billingAddressStreet,
                billingAddressNumber,
                billingAddressComplement
            ] = billingAddress.street;

            const shippingAddress = quote.shippingAddress();
            const [
                shippingAddressStreet,
                shippingAddressNumber,
                shippingAddressComplement
            ] = shippingAddress.street;

            let customerEmail = window.checkoutConfig.customerData?.email;
            if(quote.guestEmail) {
                customerEmail = quote.guestEmail;
            }

            const customerPhones =
                [{
                    country_code : '55',
                    subscriber : shippingAddress.telephone.replace(/\D/g, ''),
                    phone_type : 'mobile'
                }];

            return {
                bill_addr : {
                    street : billingAddressStreet,
                    number : billingAddressNumber,
                    complement : billingAddressComplement,
                    city : billingAddress.city,
                    state : billingAddress.regionCode,
                    country : 'BRA',
                    post_code : billingAddress.postcode
                },
                ship_addr : {
                    street : shippingAddressStreet,
                    number : shippingAddressNumber,
                    complement : shippingAddressComplement,
                    city : shippingAddress.city,
                    state : shippingAddress.regionCode,
                    country : 'BRA',
                    post_code : shippingAddress.postcode
                },
                email : customerEmail,
                phones : customerPhones,
                card_expiry_date : cardExpiryDate,
                purchase : {
                    amount : Math.trunc(amountInCents),
                    date :
                        new Date().toISOString()
                    ,
                    instal_data : 2,
                },
                acct_type : acctType
            }
        }
	};
});
