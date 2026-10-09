#!/usr/bin/env bash
#
# Build or refresh a deployed Badbaado instance.
#
# Run this ON THE SERVER, from the application root, over SSH. It is written
# for the free Alwaysdata plan (no root, no persistent services) but works on
# any plain PHP host.
#
# It does NOT upload code. Get the files onto the server first (git clone or
# SFTP), and build the frontend assets on your machine with `npm run build`,
# then upload public/build — free PHP hosts rarely have a usable Node.js.
#
# What it does, in order:
#
#   1. composer install --no-dev --optimize-autoloader
#   2. php artisan migrate --force
#   3. php artisan optimize          (cache config, routes, views, events)
#   4. repair public/storage         (via scripts/fix-storage.sh)
#
# Usage:
#   bash scripts/deploy.sh                 full run
#   bash scripts/deploy.sh --no-migrate    skip migrations (config/perf only)
#   bash scripts/deploy.sh --no-optimize   skip config/route/view caching
#   PHP_BIN=php8.3 bash scripts/deploy.sh  pin the PHP binary
#
# Run it once after the first upload, and again after every code change.

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

MIGRATE=1
OPTIMIZE=1
for arg in "$@"; do
    case "$arg" in
        --no-migrate) MIGRATE=0 ;;
        --no-optimize) OPTIMIZE=0 ;;
        -h|--help) sed -n '2,27p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *) echo "Unknown option: $arg" >&2; exit 2 ;;
    esac
done

log()  { printf '%s\n' "$*"; }
warn() { printf 'WARN  %s\n' "$*" >&2; }
fail() { printf 'ERROR %s\n' "$*" >&2; }

PHP_BIN="${PHP_BIN:-php}"
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
    fail "'$PHP_BIN' was not found on PATH."
    warn "On most servers PHP runs via a versioned binary, e.g. 'php8.3'."
    warn "Re-run with PHP_BIN set, e.g. PHP_BIN=php8.3 bash scripts/deploy.sh"
    exit 1
fi

log "==> Badbaado deploy"
log "    app root: $APP_ROOT"
log "    php:      $($PHP_BIN -r 'echo PHP_VERSION;')  ($PHP_BIN)"
log ""

if [ ! -f .env ]; then
    fail ".env is missing. Copy .env.example to .env and fill it in first."
    exit 1
fi

# ---------------------------------------------------------------------------
# 1. Dependencies (production only)
# ---------------------------------------------------------------------------
if command -v composer >/dev/null 2>&1; then
    log "--> composer install --no-dev"
    composer install --no-dev --optimize-autoloader --no-interaction
else
    warn "composer was not found on PATH; skipping dependency install."
    warn "Upload a locally-built vendor/ or install composer first."
fi

# ---------------------------------------------------------------------------
# 2. Database migrations
# ---------------------------------------------------------------------------
if [ "$MIGRATE" -eq 1 ]; then
    log "--> php artisan migrate --force"
    "$PHP_BIN" artisan migrate --force
else
    log "--> migrations skipped (--no-migrate)"
fi

# ---------------------------------------------------------------------------
# 3. Cache config, routes, views, events
# ---------------------------------------------------------------------------
if [ "$OPTIMIZE" -eq 1 ]; then
    log "--> php artisan optimize"
    "$PHP_BIN" artisan optimize
else
    log "--> caching skipped (--no-optimize)"
fi

# ---------------------------------------------------------------------------
# 4. Public storage symlink + permissions
# ---------------------------------------------------------------------------
if [ -f scripts/fix-storage.sh ]; then
    log "--> bash scripts/fix-storage.sh"
    PHP_BIN="$PHP_BIN" bash scripts/fix-storage.sh
else
    warn "scripts/fix-storage.sh not found; run 'php artisan storage:link' manually."
fi

log ""
log "==> Deploy finished. Open the site and confirm the login page loads."
