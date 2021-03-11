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
        'mage/storage',
        'Magento_Checkout/js/model/url-builder'
    ],
    function (
        storage,
        urlBuilder
    ) {

        return function (brand, amount) {
            var serviceUrl;
            serviceUrl = urlBuilder.createUrl('/pagarme/installments/brandbyamount/' + brand + '/' + amount, {});
            return storage.get(
                serviceUrl, false
            )
        };
    }
);
