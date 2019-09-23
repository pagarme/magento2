var CustomerValidator = function (addressObject) {
    this.addressObject = addressObject;
    this.errors = [];
};

CustomerValidator.prototype.validate = function () {
    var address = this.addressObject;

    if (address.vatId <= 0) {
        this.errors.push("Customer document is a required field");
    }

    if (address.street.length < 3) {
        this.errors.push("Invalid address");
    }
}

CustomerValidator.prototype.getErrors = function () {
    return this.errors;
}