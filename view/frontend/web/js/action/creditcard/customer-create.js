/**
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/model/url-builder',
        'mage/url'
    ],
    function (
        $,
        storage,
        urlBuilder,
        mageUrl
    ) {

        return function (data) {
            var serviceUrl;
            serviceUrl = urlBuilder.createUrl('/pagarme/customer/create/', {});

            return $.ajax({
                method: "POST",
                beforeSend: function(request) {
                    request.setRequestHeader("Content-type", 'application/json');
                },
                url: mageUrl.build(serviceUrl),
                cache: false,
                data: JSON.stringify(data)
            });
        };
    }
);
