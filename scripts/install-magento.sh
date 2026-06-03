#!/usr/bin/env bash
# Install Magento via Composer + setup:install into the 'magento_src' named volume.
# Invoked by: make install-magento
set -euo pipefail

COMPOSE="docker compose"
APP_SERVICE="app"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"
cd "$ROOT_DIR"

# ── Helpers ────────────────────────────────────────────────────────────────────

info() { printf '\n\033[36m==>\033[0m %s\n' "$*"; }
ok()   { printf '\033[32m  ✓\033[0m %s\n'  "$*"; }
die()  { printf '\n\033[31mERROR:\033[0m %s\n' "$*" >&2; exit 1; }
hr()   { printf '\033[90m%s\033[0m\n' "────────────────────────────────────────────────────"; }

# ── .env ───────────────────────────────────────────────────────────────────────

if [ ! -f .env ]; then
    cp .env.example .env
    printf '\033[33m.env created from .env.example\033[0m — review credentials before continuing.\n'
fi

set -a
# shellcheck source=.env.example
source .env
set +a

# ── Pre-flight: containers must be running ─────────────────────────────────────

hr
printf ' Pre-flight checks\n'
hr

if ! $COMPOSE ps --status=running 2>/dev/null | grep -q "$APP_SERVICE"; then
    die "Container '$APP_SERVICE' is not running. Run 'make up' first."
fi
ok "Containers are up."

# Guard against re-running on an already-installed Magento
if $COMPOSE exec -T "$APP_SERVICE" test -f /var/www/html/bin/magento 2>/dev/null; then
    die "Magento is already installed. Run 'make clean-magento' first to start fresh."
fi
ok "Named volume is empty — ready to install."

# ── auth.json ──────────────────────────────────────────────────────────────────

hr
printf ' Magento Marketplace authentication\n'
hr

if [ ! -f auth.json ]; then
    printf '\n\033[33m  auth.json not found.\033[0m\n'
    printf '  Generate your keys at:\n'
    printf '    https://commercemarketplace.adobe.com/customer/accessKeys/\n\n'
    read -rp  "  Public key  (username): " _pub
    read -rsp "  Private key (password): " _priv
    printf '\n'

    cat > auth.json <<JSON
{
    "http-basic": {
        "repo.magento.com": {
            "username": "${_pub}",
            "password": "${_priv}"
        }
    }
}
JSON
    ok "auth.json created (already in .gitignore)."
else
    ok "auth.json found."
fi

info "Injecting auth.json into composer home inside the container..."
$COMPOSE exec -T "$APP_SERVICE" mkdir -p /root/.composer
$COMPOSE exec -T "$APP_SERVICE" bash -c "cat > /root/.composer/auth.json" < auth.json
ok "Credentials copied to container."

# ── composer create-project ────────────────────────────────────────────────────

hr
printf ' Step 1 — composer create-project\n'
hr

# Magento is installed into the 'magento_src' named volume (/var/www/html).
# Named volumes live on Docker's own ext4 VHD — no Windows drvfs ACL issues.
# All mkdir/write operations inside /var/www/html work normally as root.
# Two-step install to satisfy two conflicting requirements:
#   1. composer create-project requires the target directory to be EMPTY.
#   2. Magento's Composer plugin writes app/etc/vendor_path.php on the first
#      package install, so app/etc/ must exist before `composer install` runs.
#
# Solution: --no-install fetches only the project's composer.json (no packages),
# leaving the directory with one file. Then we create app/etc/ and run
# `composer install` separately, which installs all packages successfully.

info "Step 1a — fetching project composer.json (--no-install)..."
$COMPOSE exec "$APP_SERVICE" composer create-project \
    --repository-url=https://repo.magento.com/ \
    "magento/project-community-edition:2.4.*" \
    . \
    --no-install \
    --no-interaction \
    --no-progress
ok "Project composer.json downloaded."

info "Step 1b — creating app/etc/ before packages are installed..."
$COMPOSE exec -T "$APP_SERVICE" mkdir -p /var/www/html/app/etc
ok "app/etc/ ready."

info "Step 1c — running composer install (this can take 5-10 minutes)..."
# COMPOSER_PROCESS_TIMEOUT=0 prevents the 300 s per-process timeout that
# large packages (aws-sdk, MFTF, PageBuilder…) can exceed during unzip.
$COMPOSE exec -e COMPOSER_PROCESS_TIMEOUT=0 "$APP_SERVICE" composer install \
    --no-interaction \
    --no-progress
ok "Magento source downloaded."

# ── Install module via Composer path repository ────────────────────────────────

# A plain app/code/ symlink bypasses the Composer dependency resolver, so the
# module's own dependencies (e.g. pagarme/ecommerce-module-core) are never
# installed and the setup:install fails with "Class not found".
#
# A Composer path repository solves both problems at once:
#   • The module's dependencies are resolved and installed automatically.
#   • "symlink": true makes Composer create vendor/pagarme/…  →  /var/www/module,
#     so any edit to the bind-mounted source reflects immediately inside the
#     named volume without a container restart.
info "Registering module as Composer path repository..."
$COMPOSE exec -T "$APP_SERVICE" composer config \
    repositories.pagarme-local \
    '{"type":"path","url":"/var/www/module","options":{"symlink":true}}'

info "Installing module + its dependencies (pagarme/ecommerce-module-core, etc.)..."
$COMPOSE exec -e COMPOSER_PROCESS_TIMEOUT=0 "$APP_SERVICE" \
    composer require "pagarme/pagarme-magento2-module:*" \
    --no-interaction \
    --no-progress
ok "Module and its dependencies installed."

# ── bin/magento setup:install ──────────────────────────────────────────────────

hr
printf ' Step 2 — bin/magento setup:install\n'
hr

MAGENTO_HOST="${MAGENTO_HOST:-localhost}"
BASE_URL="http://${MAGENTO_HOST}/"

info "Running setup:install with DB and OpenSearch config from .env..."

# --search-engine=opensearch requires Magento 2.4.6+.
# OpenSearch 2.x is fully compatible; security is disabled in compose.yaml.
$COMPOSE exec "$APP_SERVICE" php bin/magento setup:install \
    --base-url="${BASE_URL}" \
    --db-host="${MAGENTO_DATABASE_HOST:-db}:${MAGENTO_DATABASE_PORT_NUMBER:-3306}" \
    --db-name="${MAGENTO_DATABASE_NAME:-magento}" \
    --db-user="${MAGENTO_DATABASE_USER:-magento}" \
    --db-password="${MAGENTO_DATABASE_PASSWORD:-magento}" \
    --admin-firstname="${MAGENTO_ADMIN_FIRSTNAME:-Admin}" \
    --admin-lastname="${MAGENTO_ADMIN_LASTNAME:-User}" \
    --admin-email="${MAGENTO_ADMIN_EMAIL:-admin@example.com}" \
    --admin-user="${MAGENTO_ADMIN_USER:-admin}" \
    --admin-password="${MAGENTO_ADMIN_PASSWORD:-Admin@12345}" \
    --language="${MAGENTO_LANGUAGE:-pt_BR}" \
    --currency="${MAGENTO_CURRENCY:-BRL}" \
    --timezone="${MAGENTO_TIMEZONE:-America/Sao_Paulo}" \
    --search-engine=opensearch \
    --opensearch-host="${ELASTICSEARCH_HOST:-opensearch}" \
    --opensearch-port="${ELASTICSEARCH_PORT_NUMBER:-9200}" \
    --use-rewrites=1 \
    --backend-frontname="${MAGENTO_ADMIN_URL:-admin}"

ok "setup:install complete."

# ── Deploy mode ────────────────────────────────────────────────────────────────

info "Setting deploy mode → '${MAGENTO_MODE:-developer}'..."
$COMPOSE exec -T "$APP_SERVICE" php bin/magento deploy:mode:set \
    "${MAGENTO_MODE:-developer}" --skip-compilation || true

# ── File permissions ───────────────────────────────────────────────────────────

info "Fixing file permissions for www-data..."
$COMPOSE exec -T "$APP_SERVICE" bash -c "
    find var generated vendor pub/static pub/media app/etc \
        -type f -exec chmod g+w {} + 2>/dev/null || true
    find var generated vendor pub/static pub/media app/etc \
        -type d -exec chmod g+ws {} + 2>/dev/null || true
    chown -R www-data:www-data /var/www/html
"
ok "Permissions set."

# ── Done ───────────────────────────────────────────────────────────────────────

printf '\n\033[32m══════════════════════════════════════════════════\033[0m\n'
printf '\033[32m  Magento installed successfully!\033[0m\n'
printf '\033[32m══════════════════════════════════════════════════\033[0m\n\n'
printf '  Storefront   →  %s\n'          "${BASE_URL}"
printf '  Admin panel  →  %s%s\n'        "${BASE_URL}" "${MAGENTO_ADMIN_URL:-admin}"
printf '  Credentials  →  %s / %s\n\n'   "${MAGENTO_ADMIN_USER:-admin}" "${MAGENTO_ADMIN_PASSWORD:-Admin@12345}"
