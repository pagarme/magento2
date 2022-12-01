FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

WORKDIR /opt/bitnami/magento
COPY auth.json /opt/bitnami/magento
RUN composer --no-interaction config minimum-stability dev
RUN composer require pagarme/pagarme-magento2-module:dev-stg
RUN /bitnami/magento/bin/magento config:set web/unsecure/base_url https://stg-magento2.mundipagg.com/
RUN /bitnami/magento/bin/magento config:set web/secure/base_url https://stg-magento2.mundipagg.com/

