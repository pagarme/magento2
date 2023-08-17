FROM bitnami/magento:2.4.3
MAINTAINER Open Source Team

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /opt/bitnami/magento
ARG MAGENTO_KEY
ARG MAGENTO_SECRET
ARG MARKETPLACE_KEY
ARG MARKETPLACE_SECRET
ARG MARKETPLACE_REPO
ARG MARKETPLACE_NAME
ARG MARKETPLACE_VERSION
ARG MARKETPLACE_REPO_URL

RUN composer config -g http-basic.repo.magento.com ${MAGENTO_KEY} ${MAGENTO_SECRET}
RUN composer require pagarme/pagarme-magento2-module:dev-stg

RUN apt update
RUN apt install git -y

RUN composer config -g repositories.marketplace_repo composer ${MARKETPLACE_REPO}
RUN composer config -g http-basic.${MARKETPLACE_REPO_URL} ${MARKETPLACE_KEY} ${MARKETPLACE_SECRET}
RUN composer require ${MARKETPLACE_NAME}:${MARKETPLACE_VERSION}