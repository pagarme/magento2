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
		constructor(formObject){
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
			const challengeWindowSize = '03'
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
        addTdsAttributeData () {
            jQuery(this.formObject.containerSelector).attr("data-pagarmecheckout-form", "")
            this.formObject.creditCardHolderName.attr("data-pagarmecheckout-element", "holder_name")
            this.formObject.creditCardNumber.attr("data-pagarmecheckout-element", "number")
            this.formObject.creditCardNumber.val("9001100811111111") // @todo: remover na versão final
            this.formObject.creditCardBrand.attr("data-pagarmecheckout-element", "brand")
            this.formObject.creditCardExpMonth.attr("data-pagarmecheckout-element", "exp_month")
            this.formObject.creditCardExpYear.attr("data-pagarmecheckout-element", "exp_year")
            this.formObject.creditCardCvv.attr("data-pagarmecheckout-element", "cvv")
        }
        removeTdsAttributeData () {
            jQuery(this.formObject.containerSelector).removeAttr("data-pagarmecheckout-form")
            this.formObject.creditCardHolderName.removeAttr("data-pagarmecheckout-element")
            this.formObject.creditCardNumber.removeAttr("data-pagarmecheckout-element")
            this.formObject.creditCardBrand.removeAttr("data-pagarmecheckout-element")
            this.formObject.creditCardExpMonth.removeAttr("data-pagarmecheckout-element")
            this.formObject.creditCardExpYear.removeAttr("data-pagarmecheckout-element")
            this.formObject.creditCardCvv.removeAttr("data-pagarmecheckout-element")
        }
        getTdsData(acctType, cardExpiryDate) {
            const billingAddress = quote.billingAddress();
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

            const customerData = window.checkoutConfig.customerData;
            const customerPhones =
                [{
                    country_code : '55',
                    subscriber : shippingAddress.telephone,
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
                email : customerData.email,
                phones : customerPhones,
                card_expiry_date : cardExpiryDate,
                purchase : {
                    amount : quote.totals().grand_total * 100,
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
