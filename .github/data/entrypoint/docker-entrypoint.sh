#!/bin/bash
set -e

MAGENTO_ROOT=/var/www/html
INSTALL_FLAG="${MAGENTO_ROOT}/var/.installed"

wait_for_db() {
    echo "[entrypoint] Waiting for MariaDB at ${MAGENTO_DATABASE_HOST}:${MAGENTO_DATABASE_PORT_NUMBER:-3306}..."
    until php -r "
        \$conn = @new mysqli(
            '${MAGENTO_DATABASE_HOST}',
            '${MAGENTO_DATABASE_USER}',
            '${MAGENTO_DATABASE_PASSWORD}',
            '${MAGENTO_DATABASE_NAME}',
            ${MAGENTO_DATABASE_PORT_NUMBER:-3306}
        );
        if (\$conn->connect_error) exit(1);
        exit(0);
    " 2>/dev/null; do
        echo "[entrypoint] DB not ready yet, retrying in 3s..."
        sleep 3
    done
    echo "[entrypoint] DB is ready."
}

wait_for_elasticsearch() {
    echo "[entrypoint] Waiting for Elasticsearch at ${ELASTICSEARCH_HOST}:${ELASTICSEARCH_PORT_NUMBER:-9200}..."
    until curl -sf "http://${ELASTICSEARCH_HOST}:${ELASTICSEARCH_PORT_NUMBER:-9200}/_cluster/health" > /dev/null 2>&1; do
        echo "[entrypoint] Elasticsearch not ready yet, retrying in 3s..."
        sleep 3
    done
    echo "[entrypoint] Elasticsearch is ready."
}

run_setup_install() {
    echo "[entrypoint] Running Magento setup:install..."

    HTTPS_ARGS=""
    if [ "${MAGENTO_ENABLE_HTTPS:-no}" = "yes" ]; then
        BASE_URL="https://${MAGENTO_HOST}/"
        HTTPS_ARGS="--use-secure=1 --base-url-secure=${BASE_URL} --use-secure-admin=1"
    else
        BASE_URL="http://${MAGENTO_HOST}/"
    fi

    php bin/magento setup:install \
        --base-url="${BASE_URL}" \
        ${HTTPS_ARGS} \
        --db-host="${MAGENTO_DATABASE_HOST}:${MAGENTO_DATABASE_PORT_NUMBER:-3306}" \
        --db-name="${MAGENTO_DATABASE_NAME}" \
        --db-user="${MAGENTO_DATABASE_USER}" \
        --db-password="${MAGENTO_DATABASE_PASSWORD}" \
        --admin-firstname="${MAGENTO_ADMIN_FIRSTNAME:-Admin}" \
        --admin-lastname="${MAGENTO_ADMIN_LASTNAME:-Admin}" \
        --admin-email="${MAGENTO_ADMIN_EMAIL:-admin@example.com}" \
        --admin-user="${MAGENTO_ADMIN_USER:-admin}" \
        --admin-password="${MAGENTO_ADMIN_PASSWORD:-Admin@12345}" \
        --language="${MAGENTO_LANGUAGE:-en_US}" \
        --currency="${MAGENTO_CURRENCY:-USD}" \
        --timezone="${MAGENTO_TIMEZONE:-America/Sao_Paulo}" \
        --search-engine=elasticsearch7 \
        --elasticsearch-host="${ELASTICSEARCH_HOST:-elasticsearch}" \
        --elasticsearch-port="${ELASTICSEARCH_PORT_NUMBER:-9200}" \
        --use-rewrites=1 \
        --backend-frontname="${MAGENTO_ADMIN_URL:-admin}"

    touch "${INSTALL_FLAG}"
    echo "[entrypoint] setup:install complete."
}

run_upgrade() {
    echo "[entrypoint] Running setup:upgrade..."
    php bin/magento setup:upgrade --keep-generated
    php bin/magento cache:flush
    echo "[entrypoint] Upgrade complete."
}

# ── Main ──────────────────────────────────────────────

cd "${MAGENTO_ROOT}"

wait_for_db
wait_for_elasticsearch

if [ ! -f "${INSTALL_FLAG}" ]; then
    run_setup_install
else
    run_upgrade
fi

# Set deploy mode
if [ -n "${MAGENTO_MODE}" ]; then
    php bin/magento deploy:mode:set "${MAGENTO_MODE}" --skip-compilation 2>/dev/null || true
fi

echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
