define([
  "Pagarme_Pagarme/js/view/payment/default",
  "https://pay.google.com/gp/p/js/pay.js",
  "Magento_Checkout/js/model/quote",
  "Pagarme_Pagarme/js/core/checkout/PaymentModuleBootstrap",
  "Pagarme_Pagarme/js/core/models/GooglePayModel"
], function (Component, googlePay, quote, PagarmeCore) {
	"use strict";
	return Component.extend({
		defaults: {
			template: "Pagarme_Pagarme/payment/googlepay",
		},
		getCode: function() {
			return "pagarme_googlepay";
		},
		getTitle: function () {
			return window.checkoutConfig.payment.pagarme_googlepay.title;
		},
		getGooglePaymentsClient: function () {
			let self = this;
			let environment = "TEST";
			if (window.checkoutConfig.pagarme_is_sandbox_mode === false) {
				environment = "PRODUCTION";
			}

			return new google.payments.api.PaymentsClient({ environment: environment });
		},
		addGooglePayButton: function () {
			let self = this;
			let paymentsClient = this.getGooglePaymentsClient();
			const button = paymentsClient.createButton({
				buttonColor: 'default',
				buttonType: 'pay',
				buttonRadius: 5,
				buttonLocale: 'pt',
				buttonSizeMode: 'fill',
			});
			document.getElementById("pagarme-googlepay").appendChild(button);
		},

		onPaymentAuthorized: function (paymentData) {
			return new Promise(function (resolve, reject) {
				processPayment(paymentData)
				.then(function () {
					resolve({ transactionState: "SUCCESS" });
				})
				.catch(function () {
					resolve({
					transactionState: "ERROR",
					error: {
						intent: "PAYMENT_AUTHORIZATION",
						message: "Insufficient funds",
						reason: "PAYMENT_DATA_INVALID",
					},
					});
				});
			});
		},

		getGooglePaymentDataRequest: function () {
			const baseRequest = {
				apiVersion: 2,
				apiVersionMinor: 0,
			};
			const tokenizationSpecification = {
				type: "PAYMENT_GATEWAY",
				parameters: {
					gateway: "pagarme",
					gatewayMerchantId: window.checkoutConfig.pagarme_account_id,
				},
			};
			console.log(tokenizationSpecification);
			const baseCardPaymentMethod = {
				type: "CARD",
				parameters: {
				allowedAuthMethods: ["PAN_ONLY"],
				allowedCardNetworks: [
					"AMEX",
					"DISCOVER",
					"JCB",
					"MASTERCARD",
					"VISA",
				],
				},
			};
			const cardPaymentMethod = Object.assign({}, baseCardPaymentMethod, {
				tokenizationSpecification: tokenizationSpecification,
			});
			const paymentDataRequest = Object.assign({}, baseRequest);
			paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
			paymentDataRequest.transactionInfo = this.getGoogleTransactionInfo();
			paymentDataRequest.merchantInfo = {
				merchantId: window.checkoutConfig.payment.pagarme_googlepay.merchantId,
				merchantName: window.checkoutConfig.payment.pagarme_googlepay.merchantName,
			};
			return paymentDataRequest;
		},

		onGooglePaymentButtonClicked: function () {
			self = this;
			const paymentDataRequest = this.getGooglePaymentDataRequest();
			paymentDataRequest.transactionInfo = this.getGoogleTransactionInfo();

			const paymentsClient = this.getGooglePaymentsClient();
			paymentsClient
				.loadPaymentData(paymentDataRequest)
				.then(function (paymentData) {
					self.processPayment(paymentData, self);
				})
				.catch(function (err) {
					// console.error(err);
				});
		},

		getGoogleTransactionInfo: function () {
			return {
				countryCode: "BR",
				currencyCode: quote.totals().base_currency_code,
				totalPriceStatus: "FINAL",
				totalPrice: quote.totals().grand_total.toFixed(2),
			};
		},

		prefetchGooglePaymentData: function () {
			const paymentDataRequest = this.getGooglePaymentDataRequest();
			paymentDataRequest.transactionInfo = {
				totalPriceStatus: "NOT_CURRENTLY_KNOWN",
				currencyCode: quote.totals().base_currency_code,
			};
			const paymentsClient = this.getGooglePaymentsClient();
			paymentsClient.prefetchPaymentData(paymentDataRequest);
		},

		processPayment: function (paymentData, self) {
			
			console.log(paymentData.paymentMethodData.tokenizationData.token);
			var paymentToken = paymentData.paymentMethodData.tokenizationData.token;
			


			var _self = self;
			_self.elementox = paymentToken;
			PagarmeCore.platFormConfig.addresses.billingAddress = quote.billingAddress();

			var PlatformPlaceOrder = {
				obj : _self,
				data: paymentToken,
				event: event
			};

			PagarmeCore.placeOrder(
				PlatformPlaceOrder,
				this.getModel()
			);
		},

		getMailingAddress: function () {
			return window.checkoutConfig.payment.checkmo.mailingAddress;
		},
		getInstructions: function () {
			return window.checkoutConfig.payment.instructions[this.item.method];
		},
		getModel: function () {
			return 'googlepay';
		},

		getData: function () {
			var paymentMethod = PagarmeCore.paymentMethod[this.getModel()];
			if (paymentMethod == undefined) {
				return paymentMethod;
			}
			var paymentModel = paymentMethod.model;
			return paymentModel.getData();
		},
	});
});
