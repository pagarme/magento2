define([], () => {
    /**
     * @typedef {Object} NxTdsResult
     * @property {string}  [risk_id]
     * @property {Array}   [steps]
     */

    /**
     * @typedef {Object} NxTdsChallengeResult
     * @property {string}  [risk_id]
     * @property {string}  [trans_status]
     * @property {string}  [tds_server_trans_id]
     * @property {boolean} [challenge_canceled]
     * @property {string}  [authenticated_card]
     * @property {string}  [error]
     */

    return class NxThreeDsChallenge {
        constructor() {
            this.name = 'nx';
        }

        /**
         * @param {string}   tdsToken - JWT token returned by the TDS provider
         * @param {Object}   tdsData  - Order and cardholder data for the TDS flow
         * @param {function(NxTdsChallengeResult): void} callback
         */
        execute(tdsToken, tdsData, callback) {
            const tdsMethodContainer = document.getElementById('tdsMethodContainer');
            const challengeContainer = document.getElementById('challengeContainer');

            if (!tdsMethodContainer || !challengeContainer) {
                throw new Error('NX TDS containers not found in DOM');
            }
            challengeContainer.style.display = 'block';
            jQuery('body').trigger('processStop');
            // AuthSwitch (tifa) flow — commented out while using NX direct
            // window.tifa.init({
            //     tds: {
            //         token: tdsToken,
            //         orderData: tdsData,
            //         tdsMethodContainerElement: tdsMethodContainer,
            //         challengeContainerElement: challengeContainer
            //     }
            // }).then((result) => {
            //     const normalized = this.normalizeResponse(result);
            //     callback(normalized);
            // }).catch((error) => {
            //     callback({ error: error.message || 'NX TDS challenge failed' });
            // });

            

            return Promise.resolve()
                .then(() => window.TDS.init({
                    token: tdsToken,
                    tds_method_container_element: tdsMethodContainer,
                    challenge_container_element: challengeContainer,
                    use_default_challenge_iframe_style: true,
                    challenge_window_size: '03'
                }, tdsData))
                .then((result) => {
                    challengeContainer.style.display = 'none';
                    console.log('[NxTDS] raw result:', JSON.stringify(result));
                    const normalized = this.normalizeResponse(result);
                    console.log('[NxTDS] normalized:', JSON.stringify(normalized));
                    callback(normalized);
                })
                .catch((error) => {
                    challengeContainer.style.display = 'none';
                    console.error('[NxTDS] error:', error);
                    callback({ error: error.message || 'NX TDS challenge failed' });
                });
        }

        /**
         * @param {NxTdsResult|null} response
         * @returns {NxTdsChallengeResult}
         */
        normalizeResponse(response) {
            if (!response) {
                return { error: 'Empty response from NX TDS' };
            }

            // The SDK returns an array of TDS step results directly.
            const tdsItem = Array.isArray(response) ? response[0] : response;

            if (!tdsItem) {
                return { error: 'Empty response from NX TDS' };
            }

            const normalized = {};

            if (tdsItem.risk_id) {
                normalized.risk_id = tdsItem.risk_id;
            }

            if (tdsItem.trans_status !== undefined) {
                normalized.trans_status = tdsItem.trans_status;
            }

            if (tdsItem.tds_server_trans_id) {
                normalized.tds_server_trans_id = tdsItem.tds_server_trans_id;
            }

            if (tdsItem.challenge_canceled !== undefined) {
                normalized.challenge_canceled = tdsItem.challenge_canceled;
            }

            if (tdsItem.authenticated_card) {
                normalized.authenticated_card = tdsItem.authenticated_card;
            }

            return normalized;
        }
    };
});
