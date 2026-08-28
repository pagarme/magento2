define([
    "Magento_Checkout/js/model/url-builder",
    "mage/url",
    'Magento_Checkout/js/model/quote',
    'Pagarme_Pagarme/js/core/checkout/tds/NxThreeDsChallenge'
], (
    urlBuilder,
    mageUrl,
    quote,
    NxThreeDsChallenge
) => {
    /**
     * @typedef {Object} TdsTokenResponse
     * @property {string} tds_token - JWT token returned by the TDS provider
     */

    /**
     * @typedef {Object} TdsData
     * @property {Object} bill_addr
     * @property {Object} ship_addr
     * @property {string} email
     * @property {Array}  phones
     * @property {string} card_expiry_date
     * @property {Object} purchase
     * @property {string} acct_type
     */

    /**
     * @typedef {Object} TdsChallengeResult
     * @property {string}  [risk_id]
     * @property {string}  [trans_status]
     * @property {string}  [tds_server_trans_id]
     * @property {boolean} [challenge_canceled]
     * @property {string}  [authenticated_card]
     * @property {string}  [error]
     */

    return class Tds {
        constructor(formObject) {
            this.formObject = formObject;
            this.challenge = new NxThreeDsChallenge();
        }

        /**
         * Fetches a TDS token from the Magento REST endpoint.
         * The Magento REST layer serializes the PHP associative array as a
         * JSON array, so the response shape is [provider, tds_token].
         *
         * @returns {jQuery.jqXHR<[string, string]>}
         */
        getToken() {
            const url = urlBuilder.createUrl("/pagarme/tdstoken", {});
            return jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: mageUrl.build(url),
                async: false,
                cache: false,
            });
        }

        /**
         * @param {[string, string]|TdsTokenResponse} tokenResponse
         * @param {TdsData}   tdsData
         * @param {function(TdsChallengeResult): void} callbackTds
         */
        callTdsFunction(tokenResponse, tdsData, callbackTds) {
            // Magento REST serializes associative arrays as indexed arrays.
            // Shape: [tds_token] — tds_token is at index 0.
            const tdsToken = Array.isArray(tokenResponse)
                ? tokenResponse[0]
                : tokenResponse.tds_token;

            try {
                this.challenge.execute(tdsToken, tdsData, (result) => {
                    callbackTds(result);
                });
            } catch (e) {
                callbackTds({ error: e.message || 'Failed to initialize TDS challenge' });
            }
        }

        showErrors(errors, parentObject) {
            if(errors.email) {
                parentObject.addErrors("Ocorreu um problema ao encontrar o e-mail.");
            }
            if(errors.bill_addr) {
                parentObject.addErrors("Ocorreu um problema ao encontrar os endereços.");
            }
            if(errors.card_expiry_date) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de expiração do cartão.");
            }
            if(errors.purchase) {
                parentObject.addErrors("Ocorreu um problema ao montar o dado de compra.");
            }
            if(errors.message) {
                parentObject.addErrors(errors.message);
            }
        }

        addTdsAttributeData() {
            const cardForm = this.formObject;
            jQuery(cardForm.containerSelector).attr("data-pagarmecheckout-form", "");
            cardForm.creditCardHolderName.attr("data-pagarmecheckout-element", "holder_name");
            cardForm.creditCardNumber.attr("data-pagarmecheckout-element", "number");
            cardForm.creditCardBrand.attr("data-pagarmecheckout-element", "brand");
            cardForm.creditCardExpMonth.attr("data-pagarmecheckout-element", "exp_month");
            cardForm.creditCardExpYear.attr("data-pagarmecheckout-element", "exp_year");
            cardForm.creditCardCvv.attr("data-pagarmecheckout-element", "cvv");
        }

        removeTdsAttributeData() {
            const cardForm = this.formObject;
            jQuery(cardForm.containerSelector).removeAttr("data-pagarmecheckout-form");
            cardForm.creditCardHolderName.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardNumber.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardBrand.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardExpMonth.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardExpYear.removeAttr("data-pagarmecheckout-element");
            cardForm.creditCardCvv.removeAttr("data-pagarmecheckout-element");
        }

        getTdsData(acctType, cardExpiryDate) {
            const billingAddress = quote.billingAddress();
            const amountInCents = Math.trunc(quote.totals().base_grand_total * 100);

            const [
                billingAddressStreet = '',
                billingAddressNumber = '',
                billingAddressComplement = '',
                billingAddressNeighborhood = ''
            ] = billingAddress.street || [];

            const shippingAddressObj = quote.shippingAddress();
            const effectiveShippingAddress = (shippingAddressObj && shippingAddressObj.street && shippingAddressObj.telephone)
                ? shippingAddressObj
                : billingAddress;

            const [
                shippingAddressStreet = '',
                shippingAddressNumber = '',
                shippingAddressComplement = '',
                shippingAddressNeighborhood = ''
            ] = (effectiveShippingAddress.street || []);

            let customerEmail = window.checkoutConfig.customerData?.email;
            if (quote.guestEmail) {
                customerEmail = quote.guestEmail;
            }

            const rawPhone = (effectiveShippingAddress.telephone || '').replace(/\D/g, '');
            const areaCode = rawPhone.slice(0, 2);
            const phoneNumber = rawPhone.slice(2);

            const expParts = cardExpiryDate.split('-');
            const expYear = parseInt(expParts[0], 10);
            const expMonth = parseInt(expParts[1], 10);

            const billingLine1 = [billingAddressNumber, billingAddressStreet, billingAddressNeighborhood].filter(Boolean).join(', ');
            const shippingLine1 = [shippingAddressNumber, shippingAddressStreet, shippingAddressNeighborhood].filter(Boolean).join(', ');

            const items = (quote.getItems() || []).map((item) => ({
                description: item.name,
                code: item.sku
            }));

            // Old tifa format — kept for reference
            // return {
            //     bill_addr: { street, number, complement, city, state, country: 'BRA', post_code },
            //     ship_addr: { ... },
            //     email, phones: [{ country_code, subscriber, phone_type }],
            //     card_expiry_date, purchase: { amount, date, instal_data }, acct_type
            // };

            return {
                payments: [{
                    payment_method: 'credit_card',
                    credit_card: {
                        card: {
                            number: this.formObject.creditCardNumber.val().replace(/\D/g, ''),
                            holder_name: this.formObject.creditCardHolderName.val(),
                            exp_month: expMonth,
                            exp_year: expYear,
                            billing_address: {
                                country: (billingAddress.countryId || 'BR').slice(0, 2),
                                state: billingAddress.regionCode,
                                city: billingAddress.city,
                                zip_code: billingAddress.postcode,
                                line_1: billingLine1,
                                line_2: billingAddressComplement
                            }
                        }
                    },
                    amount: amountInCents
                }],
                customer: {
                    name: `${billingAddress.firstname || ''} ${billingAddress.lastname || ''}`.trim(),
                    email: customerEmail,
                    document: billingAddress.vatId || '',
                    phones: {
                        mobile_phone: {
                            country_code: '55',
                            area_code: areaCode,
                            number: phoneNumber
                        }
                    }
                },
                items: items,
                shipping: {
                    recipient_name: `${effectiveShippingAddress.firstname || ''} ${effectiveShippingAddress.lastname || ''}`.trim(),
                    address: {
                        country: (effectiveShippingAddress.countryId || 'BR').slice(0, 2),
                        state: effectiveShippingAddress.regionCode,
                        city: effectiveShippingAddress.city,
                        zip_code: effectiveShippingAddress.postcode,
                        line_1: shippingLine1,
                        line_2: shippingAddressComplement
                    }
                },
                requestor_url: window.location.origin
            };
        }
	};
});
