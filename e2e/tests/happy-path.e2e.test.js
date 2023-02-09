const { test, expect } = require('@playwright/test')
const { searchProduct, selectTheProduct, addToCart, proceedCheckout } = require('../helper/helper')
const { informEmail, informFirstAndLastName } = require('../helper/pagarme_helper')

test.describe('Happy path checkout', () => {
    test.beforeEach(async ({ page}) => {
        await page.goto('/#')
    })


    test('Access the page and search the product', async ({page}) => {
        await searchProduct(page)
        await selectTheProduct(page)
        await addToCart(page)
        await proceedCheckout(page)
        await informEmail(page)
        await informFirstAndLastName(page)
    })

})