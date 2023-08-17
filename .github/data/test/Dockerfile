FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

WORKDIR /opt/bitnami/magento

RUN composer config -g http-basic.repo.magento.com ${MAGENTO_KEY} ${MAGENTO_SECRET}

RUN composer require pagarme/pagarme-magento2-module:dev-test