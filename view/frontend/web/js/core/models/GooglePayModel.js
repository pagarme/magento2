define(['Pagarme_Pagarme/js/core/validators/MultibuyerValidator'], (MultibuyerValidator) => {
    return class GooglePayModel {
        constructor(formObject) {
            this.formObject = formObject;
            this.errors = [];
        }
        placeOrder(placeOrderObject) {
            this.placeOrderObject = placeOrderObject;
            this.placeOrderObject.placeOrder();
        }
        validate() {
            return true;
        }
        addErrors(error) {
            this.errors.push({
                message: error
            })
        }
        getData() {
            
            const data = {
                'method': "pagarme_googlepay",
                'additional_data': {
                    "googleData": this.placeOrderObject?.data
                }
            };
            return data;
        }
    };
});
