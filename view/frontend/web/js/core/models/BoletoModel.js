var BoletoModel = function (formObject) {
    this.formObject = formObject;
};

BoletoModel.prototype.placeOrder = function (placeOrderObject) {
    this.placeOrderObject = placeOrderObject;
    this.placeOrderObject.placeOrder();
}