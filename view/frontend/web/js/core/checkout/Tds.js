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
            if(errors.email) {
                parentObject.addErrors("Ocorreu um problema ao encontrar o e-mail.");
            }
            if(errors.bill_addr) {
                parentObject.addErrors("Ocorreu um problema ao encontrar os endereços.");
            }
            if(errors.card_expiry_date) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de expiração do cartão.");
            }
            if(errors.purchase) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de compra.");
            }
            if(errors.message) {
                parentObject.addErrors(errors.message);
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
            const amountInCents = quote.totals().base_grand_total * 100;
            const [
                billingAddressStreet = '',
                billingAddressNumber = '',
                billingAddressComplement = ''
            ] = billingAddress.street || [];

            const shippingAddressObj = quote.shippingAddress();
            const effectiveShippingAddress = (shippingAddressObj && shippingAddressObj.street && shippingAddressObj.telephone)
                ? shippingAddressObj
                : billingAddress;

            const [
                shippingAddressStreet = '',
                shippingAddressNumber = '',
                shippingAddressComplement = ''
            ] = (effectiveShippingAddress.street || []);

            let customerEmail = window.checkoutConfig.customerData?.email;
            if(quote.guestEmail) {
                customerEmail = quote.guestEmail;
            }

            const phoneNumber = effectiveShippingAddress.telephone
                ? effectiveShippingAddress.telephone.replace(/\D/g, '')
                : '';

            const customerPhones =
                [{
                    country_code : '55',
                    subscriber : phoneNumber,
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
                    city : effectiveShippingAddress.city,
                    state : effectiveShippingAddress.regionCode,
                    country : 'BRA',
                    post_code : effectiveShippingAddress.postcode
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
