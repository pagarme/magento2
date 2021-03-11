var config = {
    map: {
        '*': {
            jquerymask: 'Pagarme_Pagarme/js/plugins/jquery.mask.min'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'Pagarme_Pagarme/js/mixin/billing-address-mixin': true
            }
        }
    }
};
