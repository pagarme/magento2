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
        'Magento_Checkout/js/model/url-builder'
    ],
    function (
        $,
        storage,
        urlBuilder
    ) {
        return function (dataJson, successCallback, failCallback) {
            var self = this;
            var serviceUrl = 'https://api.mundipagg.com/core/v1/tokens?appId=' + window.checkoutConfig.payment.ccform.pk_token;

            var getAPIData = function(url, data, success, fail) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState < 4) {
                        return;
                    }
                    if (xhr.status === 200) {
                        success.call(self, JSON.parse(xhr.responseText));
                    } else {
                        var errorObj = JSON.parse(xhr.response);
                        errorObj.statusCode = xhr.status;
                        fail.call(null, errorObj);
                    }
                };
                xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
                xhr.send(JSON.stringify(data));
                return xhr;
            };

            getAPIData(serviceUrl, dataJson, successCallback, failCallback);
        };
    }
);
