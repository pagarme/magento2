var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/billing-address': {
                'Pagarme_Pagarme/js/mixin/billing-address-mixin': true
            }
        }
    },
    shim : {
        'Pagarme_Pagarme/js/view/payment/googlepay' : {
            deps : ['googlePay']
        }
    },
    map: {
        '*': {
            pixCheckoutSuccess: 'Pagarme_Pagarme/js/view/payment/checkout/success/pix',
            googlePay: 'https://pay.google.com/gp/p/js/pay.js'
        }
    }
};
