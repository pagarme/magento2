define(
    [
        'Magento_Checkout/js/view/payment/default',
        'https://pay.google.com/gp/p/js/pay.js',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, googlePay, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Pagarme_Pagarme/payment/pagarmegooglepay'
            },

            getGooglePaymentsClient: function () {
                let self = this;
                return new google.payments.api.PaymentsClient({environment: 'TEST'});
            },
            getButton: function () {
                let paymentsClient = this.getGooglePaymentsClient();
                const button = paymentsClient.createButton({onClick: this.onGooglePaymentButtonClicked()});
                document.getElementById('goo').appendChild(button);
            },

            onPaymentAuthorized: function(paymentData) {
                return new Promise(function(resolve, reject){
              
                // handle the response
                processPayment(paymentData)
                  .then(function() {
                    resolve({transactionState: 'SUCCESS'});
                  })
                  .catch(function() {
                      resolve({
                      transactionState: 'ERROR',
                      error: {
                        intent: 'PAYMENT_AUTHORIZATION',
                        message: 'Insufficient funds',
                        reason: 'PAYMENT_DATA_INVALID'
                      }
                    });
                  });
              
                });
            },

            getGooglePaymentDataRequest: function () {
                const baseRequest = {
                    apiVersion: 2,
                    apiVersionMinor: 0
                };
                const tokenizationSpecification = {
                    type: 'PAYMENT_GATEWAY',
                    parameters: {
                        'gateway': 'pagarme',
                        'gatewayMerchantId': 'exampleGatewayMerchantId'
                    }
                };
                const baseCardPaymentMethod = {
                      type: 'CARD',
                      parameters: {
                        allowedAuthMethods: ["PAN_ONLY", "CRYPTOGRAM_3DS"],
                        allowedCardNetworks: ["AMEX", "DISCOVER", "JCB", "MASTERCARD", "VISA"]
                      }
                    };
                const cardPaymentMethod = Object.assign(
                    {},
                    baseCardPaymentMethod,
                    {
                        tokenizationSpecification: tokenizationSpecification
                    }
                );
                const paymentDataRequest = Object.assign({}, baseRequest);
                  paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
                  paymentDataRequest.transactionInfo = this.getGoogleTransactionInfo();
                  paymentDataRequest.merchantInfo = {
                    // @todo a merchant ID is available for a production environment after approval by Google
                    // See {@link https://developers.google.com/pay/api/web/guides/test-and-deploy/integration-checklist|Integration checklist}
                    // merchantId: '01234567890123456789',
                    merchantName: 'Lojinha do Fabinho'
                  };
                  return paymentDataRequest;
            },

            onGooglePaymentButtonClicked: function () {
                self = this;
                const paymentDataRequest = this.getGooglePaymentDataRequest();
                paymentDataRequest.transactionInfo = this.getGoogleTransactionInfo();

                const paymentsClient = this.getGooglePaymentsClient();
                paymentsClient.loadPaymentData(paymentDataRequest)
                    .then(function(paymentData) {
                        // handle the response
                        processPayment(paymentData);
                    })
                    .catch(function(err) {
                        // show error in developer console for debugging
                        console.error(err);
                    });
            },


            getGoogleTransactionInfo : function () {
                return {
                    countryCode: 'BR',
                    currencyCode: quote.totals().base_currency_code,
                    totalPriceStatus: 'FINAL',
                    // set to cart total
                    totalPrice: quote.totals().grand_total.toFixed(2)
                };
            },

            prefetchGooglePaymentData: function () {
              const paymentDataRequest = this.getGooglePaymentDataRequest();
              // transactionInfo must be set but does not affect cache
              paymentDataRequest.transactionInfo = {
                totalPriceStatus: 'NOT_CURRENTLY_KNOWN',
                currencyCode: quote.totals().base_currency_code
              };
              const paymentsClient = this.getGooglePaymentsClient();
              paymentsClient.prefetchPaymentData(paymentDataRequest);
            },


            processPayment: function (paymentData) {
                  // show returned data in developer console for debugging
                    console.log(paymentData);
                  // @todo pass payment token to your gateway to process payment
                  paymentToken = paymentData.paymentMethodData.tokenizationData.token;
            },



            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            }
        });
    }
);