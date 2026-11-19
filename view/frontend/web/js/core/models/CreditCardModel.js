define([
    'Pagarme_Pagarme/js/core/validators/CreditCardValidator',
    'Pagarme_Pagarme/js/core/validators/MultibuyerValidator',
    'Pagarme_Pagarme/js/core/validators/Tds3DSValidator',
    'Pagarme_Pagarme/js/core/checkout/CreditCardToken',
    'Pagarme_Pagarme/js/core/checkout/Tds',
    'Pagarme_Pagarme/js/core/checkout/tds/TdsErrorPolicy',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
], (CreditCardValidator, MultibuyerValidator, Tds3DSValidator, CreditCardToken, Tds, TdsErrorPolicy, quote, fullScreenLoader) => {
    return class CreditCardModel {
        constructor(formObject, publicKey) {
            this.formObject = formObject;
            this.publicKey = publicKey;
            this.errors = [];
        }
        placeOrder(placeOrderObject) {
            this.placeOrderObject = placeOrderObject;
            const _self = this;

            if (
                typeof _self.formObject.savedCreditCardSelect.val() != 'undefined' &&
                _self.formObject.savedCreditCardSelect.html().length > 1 &&
                _self.formObject.savedCreditCardSelect.val() != 'new' &&
                _self.formObject.savedCreditCardSelect.val() != ''
            ) {
                _self.placeOrderObject.placeOrder();
                return;
            }

            if(this.canTdsRun()) {
                const tdsValidator = new Tds3DSValidator();
                if (!tdsValidator.validate()) {
                    tdsValidator.getErrors().forEach((error) => this.addErrors(error));
                    return;
                }

                fullScreenLoader.startLoader();
                this.getCreditCardTdsToken(
                    function (tdsToken) {
                        _self.initTds(tdsToken);
                    },
                    function(error) {
                        fullScreenLoader.stopLoader();
                        _self.addErrors("Falha ao gerar Token para 3ds, tente novamente.");
                    }
                )

                return;
            }
            this.formObject.tdsReason = this.getTdsSkipReason();
            this.getCreditCardToken(
                function (data) {
                    _self.formObject.creditCardToken.val(data.id);
                    _self.placeOrderObject.placeOrder();
                },
                function (error) {
                    _self.addErrors("Cartão inválido. Por favor, verifique os dados digitados e tente novamente");
                }
            );
        }
        addErrors(error) {
            this.errors.push({
                message: error
            })
        }
        validate() {

            const creditCardValidator = new CreditCardValidator(this.formObject);
            const isCreditCardValid = creditCardValidator.validate();

            const multibuyerValidator = new MultibuyerValidator(this.formObject);
            const isMultibuyerValid = multibuyerValidator.validate();

            if (isCreditCardValid && isMultibuyerValid) {
                return true;
            }

            return false;
        }
        getCreditCardToken(success, error) {
            const modelToken = new CreditCardToken(this.formObject);
            modelToken.getToken(this.publicKey)
                .done(success)
                .fail(error);
        }
        getCreditCardTdsToken(success, error) {
            const modelTdsToken = new Tds(this.formObject);
            modelTdsToken.getToken()
                .done(success)
                .fail(error);
        }
        getTdsSkipReason() {
            const configCard = window.checkoutConfig.payment.pagarme_creditcard;
            if (configCard['tds_active'] !== true) return 'config_disabled';
            if (quote.totals().base_grand_total * 100 < configCard['tds_min_amount'] * 100) return 'amount_below_min';
            if (!this.brandIsVisaOrMaster()) return 'brand_not_supported';
            return null;
        }
        canTdsRun() {
            return this.getTdsSkipReason() === null;
        }
        brandIsVisaOrMaster() {
            return this.formObject.creditCardBrand.val() === "visa"
                || this.formObject.creditCardBrand.val() === "mastercard";
        }
        initTds(tdsToken) {
            const modelTds = new Tds(this.formObject);
            const expYear = this.formObject.creditCardExpYear.val();
            const expMonth = this.formObject.creditCardExpMonth.val().padStart(2, '0');
            const cardExpiryDate = `${expYear}-${expMonth}`;
            const tdsData = modelTds.getTdsData(cardExpiryDate);
            modelTds.callTdsFunction(tdsToken, tdsData, this.callbackTds.bind(this));
        }

        callbackTds(data) {
            if (data?.error !== undefined) {
                this.handleTdsFailure(data);
                return;
            }

            const challengeWasCancelled = data?.challenge_canceled === true;
            const isMissingTransStatus = data?.trans_status === '' || data?.trans_status === undefined;
            if (challengeWasCancelled || isMissingTransStatus) {
                fullScreenLoader.stopLoader();
                this.addErrors("A autenticação 3DS foi cancelada. Por favor, tente novamente.");
                return;
            }

            this.formObject.authentication = JSON.stringify(data);
            this.placeOrderWithCardToken();
        }

        handleTdsFailure(failure) {
            const { action, message } = TdsErrorPolicy.resolve(failure);

            if (action !== TdsErrorPolicy.Action.PROCEED_WITHOUT_3DS) {
                fullScreenLoader.stopLoader();
                this.addErrors(message);
                return;
            }

            if (this.isTdsMandatory()) {
                fullScreenLoader.stopLoader();
                this.addErrors(
                    "Seu banco não suporta autenticação 3DS obrigatória neste momento. " +
                    "Utilize outro cartão ou método de pagamento (PIX, boleto)."
                );
                return;
            }

            this.formObject.tdsReason = 'not_eligible';
            this.formObject.authentication = undefined;
            this.placeOrderWithCardToken();
        }

        isTdsMandatory() {
            return window.checkoutConfig.payment.pagarme_creditcard.tds_mode === 'mandatory';
        }

        placeOrderWithCardToken() {
            const _self = this;
            this.getCreditCardToken(
                function (data) {
                    _self.formObject.creditCardToken.val(data.id);
                    _self.placeOrderObject.placeOrder();
                },
                function (error) {
                    fullScreenLoader.stopLoader();
                    _self.addErrors("Cartão inválido. Por favor, verifique os dados digitados e tente novamente");
                }
            );
        }

        getData() {
            this.saveThiscard = 0;
            const formObject = this.formObject;

            if (formObject.saveThisCard.prop( "checked" )) {
                this.saveThiscard = 1;
            }

            let data = this.fillData();
            data.additional_data.cc_buyer_checkbox = false;

            if (
                typeof formObject.multibuyer != 'undefined' &&
                formObject.multibuyer.showMultibuyer.prop( "checked" ) == true
            ) {
                data = this.fillMultibuyerData(data);
            }

            return data;
        }
        fillData() {
            const formObject = this.formObject;

            return {
                'method': "pagarme_creditcard",
                'additional_data': {
                    'cc_type': formObject.creditCardBrand.val(),
                    'cc_last_4': this.getLastFourNumbers(),
                    'cc_exp_year': formObject.creditCardExpYear.val(),
                    'cc_exp_month': formObject.creditCardExpMonth.val(),
                    'cc_owner': formObject.creditCardHolderName.val(),
                    'cc_savecard': this.saveThiscard,
                    'cc_saved_card': formObject.savedCreditCardSelect.val(),
                    'cc_installments': formObject.creditCardInstallments.val(),
                    'cc_token_credit_card': formObject.creditCardToken.val(),
                    'cc_card_tax_amount' : formObject.creditCardInstallments.find(':selected').attr('interest'),
                    'authentication': formObject.authentication,
                    'tds_reason': formObject.tdsReason
                }
            };
        }
        fillMultibuyerData(data) {
            const multibuyer = this.formObject.multibuyer;
            const fullname = multibuyer.firstname.val() + ' ' + multibuyer.lastname.val();

            data.additional_data.cc_buyer_checkbox = 1,
                data.additional_data.cc_buyer_name = fullname,
                data.additional_data.cc_buyer_email = multibuyer.email.val(),
                data.additional_data.cc_buyer_document = multibuyer.document.val(),
                data.additional_data.cc_buyer_street_title = multibuyer.street.val(),
                data.additional_data.cc_buyer_street_number = multibuyer.number.val(),
                data.additional_data.cc_buyer_street_complement = multibuyer.complement.val(),
                data.additional_data.cc_buyer_zipcode = multibuyer.zipcode.val(),
                data.additional_data.cc_buyer_neighborhood = multibuyer.neighborhood.val(),
                data.additional_data.cc_buyer_city = multibuyer.city.val(),
                data.additional_data.cc_buyer_state = multibuyer.state.val(),
                data.additional_data.cc_buyer_home_phone = multibuyer.homePhone.val(),
                data.additional_data.cc_buyer_mobile_phone = multibuyer.mobilePhone.val()

            return data;
        }
        getLastFourNumbers() {
            const number = this.formObject.creditCardNumber.val();
            if (number !== undefined) {
                return number.slice(-4);
            }
            return "";
        }
    }
});
