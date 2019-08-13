var BoletoModel = function (formObject) {
    this.formObject = formObject;
    this.errors = [];
};

BoletoModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    this.placeOrderObject.placeOrder();
}

BoletoModel.prototype.addErrors = function (error) {
    this.errors.push({
        message: error
    })
}