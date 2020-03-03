var config = {
    map: {
        '*': {
            jquerymask: 'MundiPagg_MundiPagg/js/plugins/jquery.mask.min',
            "Magento_SalesRule/js/action/set-coupon-code": 'MundiPagg_MundiPagg/js/action/set-coupon-code',
            "Magento_SalesRule/js/action/cancel-coupon": 'MundiPagg_MundiPagg/js/action/cancel-coupon'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'MundiPagg_MundiPagg/js/model/shipping-save-processor-default-mixin': true
            }
        }
    }
};