const config = {
    map: {
        '*': {
            numberFormatter: 'Pagarme_Pagarme/js/helper/numberFormatter',
            pagarmeJqueryMask: 'Pagarme_Pagarme/js/jquery.mask.min'
        },
        shim : {
            'pagarmeJqueryMask' : {
                deps : ['jquery']
            },
            'Pagarme_Pagarme/js/core/checkout/PaymentMethodController' : {
                deps : ['pagarmeJqueryMask']
            }
        }
    }
}
