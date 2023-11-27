define(["Magento_Checkout/js/model/url-builder", "mage/url"], (
	urlBuilder,
	mageUrl
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
	};
});
