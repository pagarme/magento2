#!/bin/bash
set -e

MAGENTO_ROOT=/var/www/html
INSTALL_FLAG="${MAGENTO_ROOT}/var/.installed"
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

configure_magento() {
    local already_installed="${1:-false}"
    local env_file="${MAGENTO_ROOT}/app/etc/env.php"

    echo "[entrypoint] Configuring deployment via setup:config:set..."
    mkdir -p "${MAGENTO_ROOT}/app/etc"
    rm -f "${env_file}"

    php bin/magento setup:config:set \
        --db-host="${MAGENTO_DATABASE_HOST}:${MAGENTO_DATABASE_PORT_NUMBER:-3306}" \
        --db-name="${MAGENTO_DATABASE_NAME}" \
        --db-user="${MAGENTO_DATABASE_USER}" \
        --db-password="${MAGENTO_DATABASE_PASSWORD}" \
        --key="${MAGENTO_CRYPT_KEY}" \
        --backend-frontname="${MAGENTO_ADMIN_URL:-admin}" \
        --session-save=files \
        --lock-provider=db \
        -n

    if [ "${already_installed}" = "true" ]; then
        php -r "
            \$f = '${env_file}';
            \$c = include \$f;
            \$c['install'] = ['date' => date('D, d M Y H:i:s O')];
            \$result = file_put_contents(\$f, '<?php' . PHP_EOL . 'return ' . var_export(\$c, true) . ';' . PHP_EOL);
            if (\$result === false) {
                fwrite(STDERR, '[entrypoint] ERROR: failed to write install.date to env.php' . PHP_EOL);
                exit(1);
            }
        "
        echo "[entrypoint] install.date added to env.php."
    fi

    echo "[entrypoint] env.php ready ($(wc -c < "${env_file}") bytes)."

    echo "[entrypoint] DEBUG: verifying env.php..."
    php -r "
        \$f = '${env_file}';
        if (!file_exists(\$f)) { echo '[entrypoint] DEBUG: env.php NOT FOUND at ' . \$f . PHP_EOL; exit(1); }
        \$c = include \$f;
        if (!is_array(\$c)) { echo '[entrypoint] DEBUG: env.php did not return an array' . PHP_EOL; exit(1); }
        echo '[entrypoint] DEBUG: install.date = ' . (\$c['install']['date'] ?? 'NOT SET') . PHP_EOL;
        echo '[entrypoint] DEBUG: db.host = ' . (\$c['db']['connection']['default']['host'] ?? 'NOT SET') . PHP_EOL;
        echo '[entrypoint] DEBUG: crypt.key present = ' . (isset(\$c['crypt']['key']) ? 'yes' : 'no') . PHP_EOL;
    "

    local config_php="${MAGENTO_ROOT}/app/etc/config.php"
    echo "[entrypoint] DEBUG: config.php exists = $([ -f "${config_php}" ] && echo yes || echo no)"
    if [ -f "${config_php}" ]; then
        php -r "
            \$c = include '${config_php}';
            echo '[entrypoint] DEBUG: config.php install key = ' . (isset(\$c['install']) ? json_encode(\$c['install']) : 'NOT PRESENT') . PHP_EOL;
        "
    fi
}

is_magento_installed() {
    DB_HOST="${MAGENTO_DATABASE_HOST}" \
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
        \$r = \$conn->query('SHOW TABLES LIKE \"core_config_data\"');
        exit(\$r && \$r->num_rows > 0 ? 0 : 1);
    " 2>/dev/null
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
        --key="${MAGENTO_CRYPT_KEY}" \
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
    echo "[entrypoint] Running setup:upgrade..."
    echo "[entrypoint] DEBUG: CWD=$(pwd)"
    echo "[entrypoint] DEBUG: bin/magento realpath=$(realpath bin/magento 2>/dev/null || echo 'N/A')"
    echo "[entrypoint] DEBUG: MAGENTO_ROOT realpath=$(realpath "${MAGENTO_ROOT}" 2>/dev/null || echo 'N/A')"
    php -r "
        \$bp = dirname(dirname(realpath('/var/www/html/bin/magento')));
        echo '[entrypoint] DEBUG: Magento BP via realpath = ' . \$bp . PHP_EOL;
        \$envFile = \$bp . '/app/etc/env.php';
        echo '[entrypoint] DEBUG: env.php path Magento reads = ' . \$envFile . PHP_EOL;
        \$exists = file_exists(\$envFile);
        echo '[entrypoint] DEBUG: env.php exists at that path = ' . (\$exists ? 'YES' : 'NO') . PHP_EOL;
        if (\$exists) {
            \$d = include \$envFile;
            echo '[entrypoint] DEBUG: install.date via Magento BP = ' . (\$d['install']['date'] ?? 'NOT SET') . PHP_EOL;
        }
    "
    php bin/magento setup:upgrade --keep-generated -v \
        || echo "[entrypoint] WARN: setup:upgrade failed, continuing anyway." >&2
    php bin/magento cache:flush \
        || echo "[entrypoint] WARN: cache:flush failed, continuing anyway." >&2
    echo "[entrypoint] Upgrade step done."
}

generate_config_php() {
    local config_file="${MAGENTO_ROOT}/app/etc/config.php"
    if [ -f "${config_file}" ]; then
        echo "[entrypoint] config.php already exists, skipping generation."
        return 0
    fi
    echo "[entrypoint] Generating app/etc/config.php from module.xml files..."
    php -r "
        \$files = array_merge(
            glob('${MAGENTO_ROOT}/vendor/*/*/etc/module.xml') ?: [],
            glob('${MAGENTO_ROOT}/app/code/*/*/etc/module.xml') ?: []
        );
        \$modules = [];
        foreach (\$files as \$file) {
            \$xml = @simplexml_load_file(\$file);
            if (\$xml) foreach (\$xml->module as \$m) \$modules[(string)\$m['name']] = 1;
        }
        ksort(\$modules);
        \$out = '<?php' . PHP_EOL . 'return [' . PHP_EOL . \"    'modules' => [\" . PHP_EOL;
        foreach (\$modules as \$name => \$v) \$out .= \"        '\" . \$name . \"' => \" . \$v . \",\" . PHP_EOL;
        \$out .= '    ]' . PHP_EOL . '];' . PHP_EOL;
        file_put_contents('${config_file}', \$out);
        echo '[entrypoint] config.php generated with ' . count(\$modules) . ' modules.' . PHP_EOL;
    "
}

# ── Main ──────────────────────────────────────────────

cd "${MAGENTO_ROOT}"

wait_for_db
wait_for_elasticsearch

generate_config_php

if is_magento_installed; then
    configure_magento "true"
    run_upgrade
else
    configure_magento "false"
    run_setup_install
fi

# Set deploy mode
if [ -n "${MAGENTO_MODE}" ]; then
    if ! php bin/magento deploy:mode:set "${MAGENTO_MODE}" --skip-compilation; then
        echo "[entrypoint] WARN: deploy:mode:set failed, continuing anyway." >&2
    fi
fi

echo "[entrypoint] Deploying static content..."
php bin/magento setup:static-content:deploy -f \
    || echo "[entrypoint] WARN: static-content:deploy failed, continuing anyway." >&2

echo "[entrypoint] Starting services..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
