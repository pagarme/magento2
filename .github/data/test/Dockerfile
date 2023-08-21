FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /opt/bitnami/magento

ARG MAGENTO_KEY
ARG MAGENTO_SECRET

RUN composer config -g http-basic.repo.magento.com ${MAGENTO_KEY} ${MAGENTO_SECRET}

RUN composer require pagarme/pagarme-magento2-module:dev-test