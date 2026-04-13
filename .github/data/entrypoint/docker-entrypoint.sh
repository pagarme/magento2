#!/bin/bash
set -e

MAGENTO_ROOT=/var/www/html

WAIT_RETRIES=${WAIT_RETRIES:-30}

wait_for_db() {
    echo "[entrypoint] Waiting for MariaDB at ${MAGENTO_DATABASE_HOST}:${MAGENTO_DATABASE_PORT_NUMBER:-3306}..."
    local retries=0
    until DB_HOST="${MAGENTO_DATABASE_HOST}" \
          DB_USER="${MAGENTO_DATABASE_USER}" \
          DB_PASS="${MAGENTO_DATABASE_PASSWORD}" \
          DB_NAME="${MAGENTO_DATABASE_NAME}" \
          DB_PORT="${MAGENTO_DATABASE_PORT_NUMBER:-3306}" \
          php -r "
              \$conn = @new mysqli(
                  getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'),
                  getenv('DB_NAME'), (int) getenv('DB_PORT')
              );
              if (\$conn->connect_error) exit(1);
              exit(0);
          " 2>/dev/null; do
        retries=$(( retries + 1 ))
        if [ "$retries" -ge "$WAIT_RETRIES" ]; then
            echo "[entrypoint] ERROR: MariaDB not ready after ${WAIT_RETRIES} attempts. Aborting." >&2
            exit 1
        fi
        echo "[entrypoint] DB not ready yet, retrying in 3s... (${retries}/${WAIT_RETRIES})"
        sleep 3
    done
    echo "[entrypoint] DB is ready."
}

wait_for_elasticsearch() {
    echo "[entrypoint] Waiting for Elasticsearch at ${ELASTICSEARCH_HOST}:${ELASTICSEARCH_PORT_NUMBER:-9200}..."
    local retries=0
    until curl -sf "http://${ELASTICSEARCH_HOST}:${ELASTICSEARCH_PORT_NUMBER:-9200}/_cluster/health" > /dev/null 2>&1; do
        retries=$(( retries + 1 ))
        if [ "$retries" -ge "$WAIT_RETRIES" ]; then
            echo "[entrypoint] ERROR: Elasticsearch not ready after ${WAIT_RETRIES} attempts. Aborting." >&2
            exit 1
        fi
        echo "[entrypoint] Elasticsearch not ready yet, retrying in 3s... (${retries}/${WAIT_RETRIES})"
        sleep 3
    done
    echo "[entrypoint] Elasticsearch is ready."
}

is_magento_installed() {
    php bin/magento setup:db:status > /dev/null 2>&1
    local code=$?
    # 0 = up to date, 1 = needs upgrade → installed
    # 2 = tables missing, or any other error → not installed
    [ "$code" -eq 0 ] || [ "$code" -eq 1 ]
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
        --admin-firstname="${MAGENTO_ADMIN_FIRSTNAME}" \
        --admin-lastname="${MAGENTO_ADMIN_LASTNAME}" \
        --admin-email="${MAGENTO_ADMIN_EMAIL}" \
        --admin-user="${MAGENTO_ADMIN_USER}" \
        --admin-password="${MAGENTO_ADMIN_PASSWORD}" \
        --language="${MAGENTO_LANGUAGE:-en_US}" \
        --currency="${MAGENTO_CURRENCY:-BRL}" \
        --timezone="${MAGENTO_TIMEZONE:-America/Sao_Paulo}" \
        --search-engine=opensearch \
        --opensearch-host="${ELASTICSEARCH_HOST}" \
        --opensearch-port="${ELASTICSEARCH_PORT_NUMBER:-9200}" \
        --use-rewrites=1 \
        --backend-frontname="${MAGENTO_ADMIN_URL}"

    echo "[entrypoint] setup:install complete."
}

run_upgrade() {
    # echo "[entrypoint] Running setup:upgrade..."
    # php bin/magento setup:upgrade --keep-generated
    # php bin/magento cache:flush
    pwd
    echo "[entrypoint] Upgrade complete."
}

# ── Main ──────────────────────────────────────────────

cd "${MAGENTO_ROOT}"

wait_for_db
wait_for_elasticsearch

if is_magento_installed; then
    run_upgrade
else
    run_setup_install
fi

# Set deploy mode
if [ -n "${MAGENTO_MODE}" ]; then
    if ! php bin/magento deploy:mode:set "${MAGENTO_MODE}" --skip-compilation; then
        echo "[entrypoint] WARN: deploy:mode:set failed, continuing anyway." >&2
    fi
fi

echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
