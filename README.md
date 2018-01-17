# Magento2/Mundipagg Integration module
This is the official Magento2 module for Mundipagg integration

# Documentation
Refer to [module documentation](https://github.com/mundipagg/magento2/wiki)

# Plugin in Magento Marketplace
Coming soon :construction:

# Installation

This module is now available through *Packagist*! You don't need to specify the repository anymore.

[https://packagist.org/packages/mundipagg/mundipagg-magento2-module](https://packagist.org/packages/mundipagg/mundipagg-magento2-module)

Add the following lines into your composer.json 
```
{
	"require": {
		"mundipagg/mundipagg-magento2-module":"^1.2.4"
	}
}
```

or simply digit 
```
composer require mundipagg/mundipagg-magento2-module
```
 
Then type the following commands from your Magento root:

```
composer update
./bin/magento setup:upgrade
./bin/magento setup:di:compile
```

# Requirements
* PHP >= 5.6
* Magento >= 2.1

# Configuration

After installation has completed go to **Stores** > **Settings** > **Configuration** > **Sales** > **Payment Methods** > **Other Payment Methods** > **MundiPagg Payments**.

To learn more about how detailed configure the module, see our [wiki](https://github.com/mundipagg/magento2/wiki)

# Business/Technical Support

Please, send a e-mail to [suporte@mundipagg.com](mailto:suporte@mundipagg.com)

# How can I contribute?
Please, refer to [CONTRIBUTING](CONTRIBUTING.md)

# Found something strange or need a new feature?
Open a new Issue following our issue template [ISSUE-TEMPLATE](ISSUE-TEMPLATE.md)

# Changelog
See in [releases](https://github.com/mundipagg/magento2/releases)
