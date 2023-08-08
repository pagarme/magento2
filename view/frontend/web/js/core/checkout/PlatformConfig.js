define([], () => {
    const PlatformConfig = {
        PlatformConfig: {}
    };

    PlatformConfig.getBrands = (data, paymentMethodBrands) => {
        const availableBrands = [];

        if (paymentMethodBrands !== undefined) {
            const brands = Object.keys(paymentMethodBrands);

            for (let i = 0, len = brands.length; i < len; i++) {
                const brand = data.payment.ccform.icons[brands[i]];
                if (!brand) continue;
                const url = brand.url;

                availableBrands[i] = {
                    'title': brands[i],
                    'image': url

                };
            }
        }
        return availableBrands;
    };

    PlatformConfig.getAvaliableBrands = (data) => {
        const creditCardBrands = PlatformConfig.getBrands(
            data,
            data.payment.ccform.availableTypes.pagarme_creditcard
        );

        const voucherBrands = PlatformConfig.getBrands(
            data,
            data.payment.ccform.availableTypes.pagarme_voucher
        );

        const debitBrands = PlatformConfig.getBrands(
            data,
            data.payment.ccform.availableTypes.pagarme_debit
        );

        const twoCreditcardBrands = PlatformConfig.getBrands(
            data,
            data.payment.ccform.availableTypes.pagarme_two_creditcard
        );

        const billetCreditcardBrands = PlatformConfig.getBrands(
            data,
            data.payment.ccform.availableTypes.pagarme_billet_creditcard
        );

        return {
            'pagarme_creditcard': creditCardBrands,
            'pagarme_voucher': voucherBrands,
            'pagarme_debit': debitBrands,
            'pagarme_two_creditcard': twoCreditcardBrands,
            'pagarme_billet_creditcard': billetCreditcardBrands
        };
    };

    PlatformConfig.getSavedCreditCards = (platFormConfig) => {
        let creditCard = null;
        let twoCreditCard = null;
        let billetCreditCard = null;
        let voucherCard = null;
        let debitCard = null;

        if (
            platFormConfig.payment.pagarme_creditcard.enabled_saved_cards &&
            typeof(platFormConfig.payment.pagarme_creditcard.cards != "undefined")
        ) {
            creditCard = platFormConfig.payment.pagarme_creditcard.cards;
        }

        if (
            platFormConfig.payment.pagarme_voucher.enabled_saved_cards &&
            typeof(platFormConfig.payment.pagarme_voucher.cards != "undefined")
        ) {
            voucherCard = platFormConfig.payment.pagarme_voucher.cards;
        }

        if (
            platFormConfig.payment.pagarme_two_creditcard.enabled_saved_cards &&
            typeof(platFormConfig.payment.pagarme_two_creditcard.cards != "undefined")
        ) {
            twoCreditCard = platFormConfig.payment.pagarme_two_creditcard.cards;
        }

        if (
            platFormConfig.payment.pagarme_billet_creditcard.enabled_saved_cards &&
            typeof(platFormConfig.payment.pagarme_billet_creditcard.cards != "undefined")
        ) {
            billetCreditCard = platFormConfig.payment.pagarme_billet_creditcard.cards;
        }

        if (
            platFormConfig.payment.pagarme_debit.enabled_saved_cards &&
            typeof(platFormConfig.payment.pagarme_debit.cards != "undefined")
        ) {
            debitCard = platFormConfig.payment.pagarme_debit.cards;
        }

        return {
            "pagarme_creditcard": creditCard,
            "pagarme_two_creditcard": twoCreditCard,
            "pagarme_billet_creditcard": billetCreditCard,
            "pagarme_voucher": voucherCard,
            "pagarme_debit": debitCard
        };
    };

    PlatformConfig.bind = (platformConfig) => {
        const grandTotal = parseFloat(platformConfig.grand_total);

        const publicKey = platformConfig.payment.ccform.pk_token;

        const urls = {
            base: platformConfig.base_url,
            installments : platformConfig.moduleUrls.installments
        };

        const currency = {
            code : platformConfig.quoteData.base_currency_code,
            decimalSeparator : platformConfig.basePriceFormat.decimalSymbol,
            precision : platformConfig.basePriceFormat.precision
        };

        const text = {
            months: platformConfig.payment.ccform.months,
            years: platformConfig.payment.ccform.years
        }

        const avaliableBrands = PlatformConfig.getAvaliableBrands(platformConfig);
        const savedAllCards = PlatformConfig.getSavedCreditCards(platformConfig);

        const loader = {
            start: platformConfig.loader.startLoader,
            stop: platformConfig.loader.stopLoader
        };
        const totals = platformConfig.totalsData;

        PlatformConfig.PlatformConfig = {
            avaliableBrands: avaliableBrands,
            orderAmount : grandTotal.toFixed(platformConfig.basePriceFormat.precision),
            urls: urls,
            currency : currency,
            text: text,
            publicKey: publicKey,
            totals: totals,
            loader: loader,
            addresses: platformConfig.addresses,
            updateTotals: platformConfig.updateTotals,
            savedAllCards: savedAllCards,
            region_states: platformConfig.region_states,
            isMultibuyerEnabled: platformConfig.is_multi_buyer_enabled
        };

        return PlatformConfig.PlatformConfig;
    }

    return PlatformConfig;
});
