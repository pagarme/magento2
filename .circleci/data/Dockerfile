FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

WORKDIR /opt/bitnami/magento
COPY auth.json /opt/bitnami/magento
RUN composer --no-interaction config minimum-stability dev
RUN composer require pagarme/pagarme-magento2-module:dev-stg
RUN /opt/bitnami/magento/bin/magento setup:config:set web/unsecure/base_url https://stg-magento2.mundipagg.com/
RUN /opt/bitnami/magento/bin/magento setup:config:set web/secure/base_url https://stg-magento2.mundipagg.com/

