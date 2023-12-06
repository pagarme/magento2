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
			this.returnData = '';
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

        getTdsData(acctType) {
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
                card_expiry_date : '2025-02',
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
