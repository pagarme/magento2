define([], () => {
   return class PlatformPlaceOrder {
       constructor(platformObject, data, event) {
           this.platformObject = platformObject;
           this.data = data;
           this.event = event;
       }
       placeOrder = function() {
           return this.platformObject.placeOrder(
               this.data,
               this.event
           );
       }
   }
});
