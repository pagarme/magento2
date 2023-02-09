const { faker } = require('@faker-js/faker')

const informEmail = async page => {
    await page.click('.\_with-tooltip > #customer-email');
    await page.type('.\_with-tooltip > #customer-email', 'ramses.almeida@pagar.me');

}

const informFirstAndLastName = async page => {
    await page.getByRole('text', { name: 'firstname' }).click()
    await page.getByRole('text', { name: 'firstname' }).fill('Ramses Jose')
}


module.exports = {
    informEmail,
    informFirstAndLastName
}