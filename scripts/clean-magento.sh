#!/usr/bin/env bash
# Wipe the Magento named volume so install-magento can run again.
# Invoked by: make clean-magento
set -euo pipefail

COMPOSE="docker compose"
APP_SERVICE="app"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(dirname "$SCRIPT_DIR")"
cd "$ROOT_DIR"

ok()  { printf '\033[32m  ✓\033[0m %s\n' "$*"; }
die() { printf '\n\033[31mERROR:\033[0m %s\n' "$*" >&2; exit 1; }

if ! $COMPOSE ps --status=running 2>/dev/null | grep -q "$APP_SERVICE"; then
    die "Container '$APP_SERVICE' is not running. Run 'make up' first."
fi

printf '\033[33mWarning:\033[0m wiping magento_src named volume (Magento files only)...\n'

# The named volume lives on Docker's ext4 VHD — no Windows drvfs restrictions.
$COMPOSE exec -T "$APP_SERVICE" bash -c "
    find /var/www/html -mindepth 1 -delete 2>/dev/null || true
"
ok "magento_src volume is empty."

# Remove stale ./src/ left over from any pre-named-volume install attempts.
# Docker containers cannot write to drvfs (/mnt/c/), so we spin up a throwaway
# container with only ./src mounted and delete from inside Linux where root
# owns those files and the ACL restrictions don't apply.
if [ -d src ] && [ -n "$(ls -A src 2>/dev/null)" ]; then
    printf '\033[33mWarning:\033[0m removing stale ./src/ contents...\n'
    docker run --rm \
        -v "$(pwd)/src:/mnt/src" \
        --entrypoint bash \
        "$(docker compose images -q "$APP_SERVICE" 2>/dev/null | head -1 || echo php:8.3-fpm)" \
        -c "find /mnt/src -mindepth 1 -delete 2>/dev/null || true"
    ok "./src/ cleaned."
fi

ok "Ready — run \033[36mmake install-magento\033[0m to reinstall."
