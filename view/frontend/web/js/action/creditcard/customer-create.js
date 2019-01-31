/**
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com  Copyright
 *
 * @link        http://www.mundipagg.com
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
            serviceUrl = urlBuilder.createUrl('/mundipagg/customer/create/', {});

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
