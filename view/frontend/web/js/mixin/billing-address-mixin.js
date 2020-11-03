define([
    'jquery',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-billing-address'
],function ($, checkoutData, quote, createBillingAddress) {
    'use strict';

    return function (Component) {
        return Component.extend({
            updateAddress: function () {
                this._super();

                var addressData = null;
                if (!this.source.get('params.invalid')) {
                    addressData = checkoutData.getBillingAddressFromData();
                }

                if (addressData != null) {
                    platFormConfig.addresses.billingAddress = createBillingAddress(addressData);
                }

                return this;
            }
        });
    };
});