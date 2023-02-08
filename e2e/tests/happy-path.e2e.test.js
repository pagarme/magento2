const { test, expect } = require('@playwright/test')

test.describe('Happy path checkout', () => {
    test.beforeEach(async ({ page}) => {
        await page.goto('/')
    })


    test('Access the page and select the product', async ({page}) => {

    })

})