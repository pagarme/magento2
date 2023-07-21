define([], () => {
    return {
        formatToPrice: (number) => {
            return number.replace(/\D/g,"")
                .replace(/(\d)(\d{8})$/,"$1.$2")
                .replace(/(\d)(\d{5})$/,"$1.$2")
                .replace(/(\d)(\d{2})$/,"$1,$2");
        }
    }
})
