define([
    'Magento_Checkout/js/model/full-screen-loader'
], (fullScreenLoader) => {
    /**
     * @typedef {Object} NxTdsChallengeResult
     * @property {string}  [risk_id]
     * @property {string}  [trans_status]
     * @property {string}  [tds_server_trans_id]
     * @property {boolean} [challenge_canceled]
     * @property {string}  [error]       - human-readable failure message
     * @property {string}  [code]        - TDSError code (e.g. SCHEME_NOT_SUPPORTED)
     * @property {number}  [status]      - HTTP status reported by the 3DS API
     * @property {Object}  [details]
     * @property {string}  [request_id]
     */

    const SDK_BASE_URL = 'https://3ds-nx-js.stone.com.br';
    const MOBILE_MAX_WIDTH_PX = 767;
    const CHALLENGE_WINDOW_SIZE_DESKTOP = '03';
    const CHALLENGE_WINDOW_SIZE_FULLSCREEN = '05';
    let sdkLoading = null;

    return class NxThreeDsChallenge {
        constructor() {
            this.name = 'nx';
        }

        getSdkUrl() {
            const environment = window.checkoutConfig.pagarme_is_sandbox_mode === false ? 'live' : 'test';
            return `${SDK_BASE_URL}/${environment}/v2/3ds2.min.js`;
        }

        /**
         * Resolves once window.TDS exists, injecting the SDK script when the
         * layout did not render it.
         * @returns {Promise<void>}
         */
        loadSdk() {
            if (window.TDS) {
                return Promise.resolve();
            }
            if (sdkLoading) {
                return sdkLoading;
            }
            sdkLoading = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = this.getSdkUrl();
                script.onload = () => window.TDS
                    ? resolve()
                    : reject(new Error('NX 3DS SDK loaded but window.TDS is undefined'));
                script.onerror = () => reject(new Error(`Failed to load NX 3DS SDK from ${script.src}`));
                document.head.appendChild(script);
            }).catch((error) => {
                sdkLoading = null;
                throw error;
            });
            return sdkLoading;
        }

        /**
         * @returns {string} challenge_window_size recommended for the current device
         */
        getChallengeWindowSize() {
            return window.innerWidth <= MOBILE_MAX_WIDTH_PX
                ? CHALLENGE_WINDOW_SIZE_FULLSCREEN
                : CHALLENGE_WINDOW_SIZE_DESKTOP;
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

            const stopObservingChallenge = this.stopLoaderWhenChallengeAppears(challengeContainer);

            return this.loadSdk()
                .then(() => window.TDS.init({
                    token: tdsToken,
                    tds_method_container_element: tdsMethodContainer,
                    challenge_container_element: challengeContainer,
                    use_default_challenge_iframe_style: false,
                    challenge_window_size: this.getChallengeWindowSize()
                }, tdsData))
                .then((result) => {
                    stopObservingChallenge();
                    callback(this.normalizeResponse(result));
                })
                .catch((error) => {
                    stopObservingChallenge();
                    console.error('[Pagarme 3DS] TDS.init failed', error);
                    callback(this.normalizeError(error));
                });
        }

        /**
         * The SDK only inserts an iframe when the issuer asks for a challenge.
         * Keep the checkout loader up during the frictionless steps and drop it
         * as soon as the iframe shows up, so it does not cover the challenge.
         *
         * @param {HTMLElement} challengeContainer
         * @returns {function(): void} stops observing
         */
        stopLoaderWhenChallengeAppears(challengeContainer) {
            const observer = new MutationObserver(() => {
                if (challengeContainer.querySelector('iframe')) {
                    fullScreenLoader.stopLoader();
                    observer.disconnect();
                }
            });
            observer.observe(challengeContainer, { childList: true, subtree: true });
            return () => observer.disconnect();
        }

        /**
         * @param {Array|Object|null} response - the SDK resolves with an array, one item per card
         * @returns {NxTdsChallengeResult}
         */
        normalizeResponse(response) {
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

            return normalized;
        }

        /**
         * Keeps the TDSError metadata so callers can decide by code instead of by message.
         *
         * @param {Error|Object} error
         * @returns {NxTdsChallengeResult}
         */
        normalizeError(error) {
            const failure = { error: (error && error.message) || 'NX TDS challenge failed' };
            const isTdsError = error && typeof error.getCode === 'function';

            if (isTdsError) {
                failure.code = error.getCode();
                failure.status = error.getStatus();
                failure.details = error.getDetails();
                failure.request_id = error.getRequestID();
            }

            return failure;
        }
    };
});
