define(
    [],
    function () {

        var states = [
            {value: 'AC', name: 'Acre'},
            {value: 'AL', name: 'Alagoas'},
            {value: 'AP', name: 'Amapá'},
            {value: 'AM', name: 'Amazonas'},
            {value: 'BA', name: 'Bahia'},
            {value: 'CE', name: 'Ceará'},
            {value: 'DF', name: 'Distrito Federal'},
            {value: 'ES', name: 'Espírito Santo'},
            {value: 'GO', name: 'Goiás'},
            {value: 'MA', name: 'Maranhão'},
            {value: 'MT', name: 'Mato Grosso'},
            {value: 'MS', name: 'Mato Grosso do Sul'},
            {value: 'MG', name: 'Minas Gerais'},
            {value: 'PA', name: 'Pará'},
            {value: 'PB', name: 'Paraíba'},
            {value: 'PR', name: 'Paraná'},
            {value: 'PE', name: 'Pernambuco'},
            {value: 'PI', name: 'Piauí'},
            {value: 'RJ', name: 'Rio de Janeiro'},
            {value: 'RN', name: 'Rio Grande do Norte'},
            {value: 'RS', name: 'Rio Grande do Sul'},
            {value: 'RO', name: 'Rondônia'},
            {value: 'RR', name: 'Roraima'},
            {value: 'SC', name: 'Santa Catarina'},
            {value: 'SP', name: 'São Paulo'},
            {value: 'SE', name: 'Sergipe'},
            {value: 'TO', name: 'Tocantins'}
        ];

        return {
            /**
             * Get states
             * @returns {Array}
             */
            getStateOptions: function () {
                return states;
            }
        };
    }
);
