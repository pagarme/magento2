FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

WORKDIR /opt/bitnami/magento
COPY auth.json /opt/bitnami/magento
RUN composer require pagarme/pagarme-magento2-module:dev-test