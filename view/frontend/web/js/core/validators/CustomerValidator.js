define([], () => {
    return class CustomerValidator {
        constructor(addressObject) {
            this.addressObject = addressObject;
            this.errors = [];
        }
        validate() {
            const address = this.addressObject;

            if (address == null) {
                this.errors.push("Customer address is required");
                return;
            }

            if (address.vatId <= 0 && address.vatId != null) {
                this.errors.push("O campo CPF/CNPJ é obrigatório.");
            }

            if (address.street.length < 3) {
                this.errors.push(
                    "O endereço fornecido está diferente do esperado. " +
                    "Verifique se você preencheu os campos " +
                    "rua, número e bairro e tente novamente."
                );
            }
        }
        getErrors() {
            return this.errors;
        }
    }
});
