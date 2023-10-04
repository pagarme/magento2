var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'Pagarme_Pagarme/js/mixin/billing-address-mixin': true
            }
        }
    },
    map: {
        '*': {
            pixCheckoutSuccess: 'Pagarme_Pagarme/js/view/payment/checkout/success/pix'
        }
    }
};
