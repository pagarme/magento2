FROM bitnami/magento:2.4.5
MAINTAINER Open Source Team

WORKDIR /opt/bitnami/magento
COPY auth.json /opt/bitnami/magento
RUN composer --no-interaction config minimum-stability dev
RUN composer require pagarme/pagarme-magento2-module:dev-stg
