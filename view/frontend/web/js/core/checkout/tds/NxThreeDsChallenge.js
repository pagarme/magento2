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

            challengeContainer.style.display = 'block';
            jQuery('body').trigger('processStop');

            window.TDS.init({
                token: tdsToken,
                tds_method_container_element: tdsMethodContainer,
                challenge_container_element: challengeContainer,
                use_default_challenge_iframe_style: true,
                challenge_window_size: '03'
            }, tdsData).then((result) => {
                challengeContainer.style.display = 'none';
                console.log('[NxTDS] raw result:', JSON.stringify(result));
                const normalized = this.normalizeResponse(result);
                console.log('[NxTDS] normalized:', JSON.stringify(normalized));
                callback(normalized);
            }).catch((error) => {
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

            const normalized = {};

            if (response.risk_id) {
                normalized.risk_id = response.risk_id;
            }

            if (response.steps && response.steps.tds && Array.isArray(response.steps.tds)) {
                const tdsList = response.steps.tds;
                if (tdsList.length > 0) {
                    const tdsData = tdsList[0];

                    if (tdsData.trans_status !== undefined) {
                        normalized.trans_status = tdsData.trans_status;
                    }

                    if (tdsData.tds_server_trans_id) {
                        normalized.tds_server_trans_id = tdsData.tds_server_trans_id;
                    }

                    if (tdsData.challenge_canceled !== undefined) {
                        normalized.challenge_canceled = tdsData.challenge_canceled;
                    }

                    if (tdsData.authenticated_card) {
                        normalized.authenticated_card = tdsData.authenticated_card;
                    }
                }
            }

            return normalized;
        }
    };
});
