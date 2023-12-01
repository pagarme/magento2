define([
    "Magento_Checkout/js/model/url-builder",
    "mage/url",
    'Magento_Checkout/js/model/quote'
], (
	urlBuilder,
	mageUrl,
    quote
) => {
	return class TdsToken {
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

        getTdsData(acctType) {
            const billingAddress = quote.billingAddress();
            const [
                billingAddressStreet,
                billingAddressNumber,
                billingAddressComplement,
                billingAddressNeighbourhood
            ] = billingAddress.street;


            const shippingAddress = quote.shippingAddress();
            const [
                shippingAddressStreet,
                shippingAddressNumber,
                shippingAddressComplement,
                shippingAddressNeighbourhood
            ] = shippingAddress.street;

            const customerData = window.checkoutConfig.customerData;
            const customerPhones = customerData.addresses.map(function(item) {
                return {
                    country_code : '55',
                    subscriber : item.telephone,
                    phone_type : 'mobile'
                }
            });

            const dateNow = new Date();

            return {
                bill_addr : {
                    street : billingAddressStreet,
                    number : billingAddressNumber,
                    complement : billingAddressComplement,
                    city : billingAddress.city,
                    state : `${billingAddress.countryId}-${billingAddress.regionCode}`,
                    country : billingAddress.countryId,
                    post_code : billingAddress.postcode
                },
                ship_addr : {
                    street : shippingAddressStreet,
                    number : shippingAddressNumber,
                    complement : shippingAddressComplement,
                    city : shippingAddress.city,
                    state : `${shippingAddress.countryId}-${shippingAddress.regionCode}`,
                    country : shippingAddress.countryId,
                    post_code : shippingAddress.postcode
                },
                email : customerData.email,
                phones : customerPhones,
                purchase : {
                    amount : quote.totals().grand_total * 100,
                    date : new Date(
                        Date.UTC(
                            dateNow.getUTCFullYear(),
                            dateNow.getUTCMonth(),
                            dateNow.getUTCDate(),
                            dateNow.getUTCHours(),
                            dateNow.getUTCMinutes(),
                            dateNow.getUTCSeconds(),
                            dateNow.getUTCMilliseconds()
                        )).format('yyyymmddhhmmss'),
                    instal_data : 2,
                },
                acct_type : acctType
            }
        }
	};
});
