const config = {
    map: {
        '*': {
            numberFormatter: 'Pagarme_Pagarme/js/helper/numberFormatter',
            pagarmeJqueryMask: 'Pagarme_Pagarme/js/jquery.mask.min'
        },
        shim : {
            'Pagarme_Pagarme/js/view/payment/googlepay' : {
                deps : ['googlePay']
            },
            'pagarmeJqueryMask' : {
                deps : ['jquery']
            },
            'Pagarme_Pagarme/js/core/checkout/PaymentMethodController' : {
                deps : ['pagarmeJqueryMask']
            }
        }
    }
}
