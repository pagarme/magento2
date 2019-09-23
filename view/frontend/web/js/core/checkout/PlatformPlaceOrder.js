const PlatformPlaceOrder = function (platformObject, data, event) {
    this.platformObject = platformObject;
    this.data = data;
    this.event = event;
}

PlatformPlaceOrder.prototype.placeOrder = function() {
    return this.platformObject.placeOrder(
        this.data,
        this.event
    );
}