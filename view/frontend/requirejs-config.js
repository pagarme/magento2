var config = {
    map: {
        '*': {
            jquerymask: 'MundiPagg_MundiPagg/js/plugins/jquery.mask.min'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'MundiPagg_MundiPagg/js/mixin/billing-address-mixin': true
            }
        }
    }
};
