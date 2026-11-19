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
     */

    /**
     * The 3DS token is only valid for a short time, so the SDK is fetched ahead of
     * the place-order click instead of between token generation and TDS.init.
     */
    const preloadSdkWhenTdsIsActive = () => {
        const creditCardConfig = window.checkoutConfig?.payment?.pagarme_creditcard;
        if (!creditCardConfig || creditCardConfig.tds_active !== true) {
            return;
        }
        new NxThreeDsChallenge().loadSdk().catch((error) => {
            console.error('[Pagarme 3DS] SDK preload failed', error);
        });
    };
    preloadSdkWhenTdsIsActive();

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
                cache: false,
            });
        }

        /**
         * @param {[string, string]|TdsTokenResponse} tokenResponse
         * @param {TdsData}   tdsData
         * @param {function(NxTdsChallengeResult): void} callbackTds
         */
        callTdsFunction(tokenResponse, tdsData, callbackTds) {
            // Magento REST serializes associative arrays as indexed arrays.
            // Shape: [tds_token] — tds_token is at index 0.
            const tdsToken = Array.isArray(tokenResponse)
                ? tokenResponse[0]
                : tokenResponse.tds_token;

            try {
                return this.challenge.execute(tdsToken, tdsData, callbackTds);
            } catch (e) {
                callbackTds({ error: e.message || 'Failed to initialize TDS challenge' });
            }
        }

        /**
         * Same amount the backend sends on order creation (grand total minus tax),
         * rounded to avoid floating point losses such as 19.99 * 100 = 1998.99...
         *
         * @returns {number}
         */
        getAmountInCents() {
            const totals = quote.totals();
            return Math.round((totals.grand_total - (totals.base_tax_amount || 0)) * 100);
        }

        getTdsData(cardExpiryDate) {
            const billingAddress = quote.billingAddress();
            const amountInCents = this.getAmountInCents();

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
                    document: (billingAddress.vatId || '').replace(/\D/g, ''),
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
