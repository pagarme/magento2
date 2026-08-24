define([
    'Magento_Checkout/js/model/quote'
], (quote) => {
    return class Tds3DSValidator {
        constructor() {
            this.errors = [];
        }

        validate() {
            this.errors = [];

            if (!this.validateBillingAddress()) {
                return false;
            }

            if (!this.validatePhones()) {
                return false;
            }

            if (!this.validateEmail()) {
                return false;
            }

            return true;
        }

        validateBillingAddress() {
            const billingAddress = quote.billingAddress();

            if (!billingAddress) {
                this.errors.push("Endereço de cobrança é obrigatório para autenticação 3DS.");
                return false;
            }

            if (!billingAddress.street || billingAddress.street.length < 2) {
                this.errors.push(
                    "Endereço incompleto para autenticação 3DS. " +
                    "Verifique se você preencheu os campos rua e número e tente novamente."
                );
                return false;
            }

            if (!billingAddress.city || billingAddress.city.trim() === '') {
                this.errors.push("Cidade do endereço de cobrança é obrigatória para autenticação 3DS.");
                return false;
            }

            if (!billingAddress.regionCode || billingAddress.regionCode.trim() === '') {
                this.errors.push("Estado do endereço de cobrança é obrigatório para autenticação 3DS.");
                return false;
            }

            if (!billingAddress.postcode || billingAddress.postcode.trim() === '') {
                this.errors.push("CEP do endereço de cobrança é obrigatório para autenticação 3DS.");
                return false;
            }

            return true;
        }

        validatePhones() {
            const billingAddress = quote.billingAddress();
            const shippingAddress = quote.shippingAddress();

            const billingPhone = billingAddress && billingAddress.telephone
                ? billingAddress.telephone.replace(/\D/g, '')
                : '';
            const shippingPhone = shippingAddress && shippingAddress.telephone
                ? shippingAddress.telephone.replace(/\D/g, '')
                : '';

            const hasValidPhone = (billingPhone.length >= 10) || (shippingPhone.length >= 10);

            if (!hasValidPhone) {
                this.errors.push(
                    "Telefone inválido para autenticação 3DS. " +
                    "Verifique se você preencheu um telefone válido com pelo menos 10 dígitos."
                );
                return false;
            }

            return true;
        }

        validateEmail() {
            let customerEmail = window.checkoutConfig.customerData?.email;
            if (!customerEmail || customerEmail.trim() === '') {
                customerEmail = quote.guestEmail;
            }

            if (!customerEmail || customerEmail.trim() === '') {
                this.errors.push("E-mail é obrigatório para autenticação 3DS.");
                return false;
            }

            const emailRegex = /\S+@\S+\.\S+/;
            if (!emailRegex.test(customerEmail)) {
                this.errors.push("E-mail inválido para autenticação 3DS.");
                return false;
            }

            return true;
        }

        getErrors() {
            return this.errors;
        }
    }
});
