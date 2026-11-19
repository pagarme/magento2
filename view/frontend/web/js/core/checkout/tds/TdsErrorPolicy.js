define([], () => {
    /**
     * Decides what the checkout does when the NX 3DS SDK fails.
     * Error codes come from the 3ds-nx-js TDSError (see the 3DS integration guide).
     */
    const Action = Object.freeze({
        PROCEED_WITHOUT_3DS: 'proceed_without_3ds',
        BLOCK: 'block',
        RETRY: 'retry'
    });

    const NOT_ELIGIBLE_CODES = ['SCHEME_NOT_SUPPORTED', 'INVALID_PROTOCOL_VERSION'];
    const CARD_REFUSED_CODES = ['UNAUTHORIZED', 'BIN_FRAUD_SUSPECT'];
    const TEMPORARY_FAILURE_CODES = [
        'TDS_RESPONSE_ERROR',
        'BAD_GATEWAY',
        'SERVICE_UNAVAILABLE',
        'INTERNAL_SERVER_ERROR'
    ];

    const Message = Object.freeze({
        CARD_REFUSED: 'Este cartão não pode ser utilizado. Por favor, utilize outro cartão.',
        TEMPORARY_FAILURE: 'Erro temporário na autenticação 3DS. Por favor, tente novamente em alguns instantes.',
        GENERIC: 'Não foi possível concluir a autenticação 3DS. Por favor, tente novamente.'
    });

    return {
        Action,

        /**
         * @param {{error?: string, code?: string}} failure
         * @returns {{action: string, message: string}}
         */
        resolve(failure) {
            const code = failure && failure.code;

            if (NOT_ELIGIBLE_CODES.includes(code)) {
                return { action: Action.PROCEED_WITHOUT_3DS, message: '' };
            }
            if (CARD_REFUSED_CODES.includes(code)) {
                return { action: Action.BLOCK, message: Message.CARD_REFUSED };
            }
            if (TEMPORARY_FAILURE_CODES.includes(code)) {
                return { action: Action.RETRY, message: Message.TEMPORARY_FAILURE };
            }
            return { action: Action.BLOCK, message: Message.GENERIC };
        }
    };
});
