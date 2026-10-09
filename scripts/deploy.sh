#!/usr/bin/env bash
#
# Redeploy a Badbaado instance in place. Safe to run repeatedly.
#
# Run ON THE SERVER. It finds the app root from its own location, so you can
# call it from anywhere:
#
#   bash ~/www/badbaado/scripts/deploy.sh              full redeploy
#   bash ~/www/badbaado/scripts/deploy.sh --no-pull    deploy the checked-out code as-is
#   bash ~/www/badbaado/scripts/deploy.sh --no-build   skip npm install/build (keep current assets)
#   bash ~/www/badbaado/scripts/deploy.sh --no-migrate skip database migrations
#   bash ~/www/badbaado/scripts/deploy.sh --no-backup  skip the pre-migrate database snapshot
#   bash ~/www/badbaado/scripts/deploy.sh --no-down    do not enter maintenance mode
#   PHP_BIN=php8.4 bash ~/www/badbaado/scripts/deploy.sh
#
# Pipeline (each step is skipped cleanly when its tool is missing):
#
#    1. enter maintenance mode   (always lifted again, even if a step fails)
#    2. clear compiled caches
#    3. snapshot the database     (rollback safety net; keeps the last 5)
#    4. git pull --ff-only        (fast-forward only; never rewrites history)
#    5. composer install --no-dev
#    6. npm install (only if needed) + npm run build
#    7. php artisan migrate --force
#    8. repair public/storage      (scripts/fix-storage.sh)
#    9. php artisan optimize       (recache config, routes, views, events)
#   10. lift maintenance mode
#
# This script does NOT commit or push your code. Commit and push locally first;
# the server pulls from GitHub.

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

DO_DOWN=1
DO_PULL=1
DO_COMPOSER=1
DO_BUILD=1
DO_MIGRATE=1
DO_BACKUP=1
DO_OPTIMIZE=1

for arg in "$@"; do
    case "$arg" in
        --no-down) DO_DOWN=0 ;;
        --no-pull) DO_PULL=0 ;;
        --no-composer) DO_COMPOSER=0 ;;
        --no-build|--no-npm) DO_BUILD=0 ;;
        --no-migrate) DO_MIGRATE=0 ;;
        --no-backup) DO_BACKUP=0 ;;
        --no-optimize) DO_OPTIMIZE=0 ;;
        -h|--help) sed -n '2,32p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *) echo "Unknown option: $arg" >&2; exit 2 ;;
    esac
done

log()  { printf '%s\n' "$*"; }
step() { printf '\n==> %s\n' "$*"; }
warn() { printf 'WARN  %s\n' "$*" >&2; }
fail() { printf 'ERROR %s\n' "$*" >&2; }

PHP_BIN="${PHP_BIN:-php}"
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
    fail "'$PHP_BIN' was not found on PATH."
    warn "On most hosts PHP runs via a versioned binary, e.g. 'php8.4'."
    warn "Re-run with PHP_BIN set: PHP_BIN=php8.4 bash scripts/deploy.sh"
    exit 1
fi
if [ ! -f .env ]; then
    fail ".env is missing in $APP_ROOT. Copy .env.example to .env and fill it in first."
    exit 1
fi
if [ ! -f artisan ]; then
    fail "artisan is missing; $APP_ROOT does not look like the application root."
    exit 1
fi

# Always lift maintenance mode on the way out, however we leave.
SITE_DOWN=0
bring_up() {
    if [ "$SITE_DOWN" -eq 1 ]; then
        if "$PHP_BIN" artisan up >/dev/null 2>&1; then
            log "Site is back UP."
        else
            warn "could not lift maintenance mode automatically; run: $PHP_BIN artisan up"
        fi
    fi
}
trap bring_up EXIT

# Read a key from .env without sourcing it (values may contain spaces or #).
env_get() {
    grep -E "^$1=" .env | tail -n1 | cut -d= -f2- | tr -d '"' | tr -d "'" || true
}

log "Badbaado redeploy"
log "  app root: $APP_ROOT"
log "  php:      $("$PHP_BIN" -r 'echo PHP_VERSION;')  ($PHP_BIN)"

# ---------------------------------------------------------------------------
# 1. Maintenance mode
# ---------------------------------------------------------------------------
if [ "$DO_DOWN" -eq 1 ]; then
    step "Entering maintenance mode"
    if "$PHP_BIN" artisan down --retry=30 >/dev/null 2>&1; then
        SITE_DOWN=1
        log "site is DOWN (visitors see a 503)"
    else
        warn "could not enter maintenance mode; continuing without it"
    fi
fi

# ---------------------------------------------------------------------------
# 2. Clear compiled caches
# ---------------------------------------------------------------------------
step "Clearing caches"
"$PHP_BIN" artisan optimize:clear >/dev/null 2>&1 && log "caches cleared" || warn "cache clear reported an issue (continuing)"

# ---------------------------------------------------------------------------
# 3. Database snapshot
# ---------------------------------------------------------------------------
if [ "$DO_BACKUP" -eq 1 ]; then
    step "Snapshotting the database"
    mkdir -p storage/app/backups
    ts="$(date +%Y%m%d-%H%M%S)"
    conn="$(env_get DB_CONNECTION)"; conn="${conn:-sqlite}"

    if [ "$conn" = "sqlite" ]; then
        dbpath="$(env_get DB_DATABASE)"
        dbpath="${dbpath:-database/database.sqlite}"
        case "$dbpath" in
            /*) ;;
            *) dbpath="$APP_ROOT/$dbpath" ;;
        esac
        if [ -f "$dbpath" ]; then
            cp -p "$dbpath" "storage/app/backups/db-$ts.sqlite"
            log "snapshot: storage/app/backups/db-$ts.sqlite"
            ls -1t storage/app/backups/db-*.sqlite 2>/dev/null | tail -n +6 | xargs -r rm -f
        else
            warn "sqlite database not found at $dbpath; skipping snapshot"
        fi
    elif [ "$conn" = "mysql" ] && command -v mysqldump >/dev/null 2>&1; then
        db_host="$(env_get DB_HOST)"; db_port="$(env_get DB_PORT)"
        db_name="$(env_get DB_DATABASE)"; db_user="$(env_get DB_USERNAME)"; db_pass="$(env_get DB_PASSWORD)"
        if MYSQL_PWD="$db_pass" mysqldump --no-tablespaces \
                -h "${db_host:-127.0.0.1}" -P "${db_port:-3306}" -u "$db_user" "$db_name" \
                > "storage/app/backups/db-$ts.sql" 2>/dev/null; then
            log "snapshot: storage/app/backups/db-$ts.sql"
            ls -1t storage/app/backups/db-*.sql 2>/dev/null | tail -n +6 | xargs -r rm -f
        else
            warn "mysqldump failed; skipping snapshot"
        fi
    else
        warn "no snapshot tool for connection '$conn'; skipping"
    fi
fi

# ---------------------------------------------------------------------------
# 4. Pull latest code (fast-forward only)
# ---------------------------------------------------------------------------
if [ "$DO_PULL" -eq 1 ]; then
    step "Pulling latest code"
    if git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        if [ -n "$(git status --porcelain)" ]; then
            warn "server working tree has local changes; commit or stash them or the pull will fail"
        fi
        if git pull --ff-only; then
            log "now at: $(git log --oneline -1)"
        else
            fail "git pull --ff-only failed (diverged history or a conflict)."
            exit 1
        fi
    else
        warn "not a git checkout; skipping pull"
    fi
fi

# ---------------------------------------------------------------------------
# 5. Composer dependencies
# ---------------------------------------------------------------------------
if [ "$DO_COMPOSER" -eq 1 ]; then
    step "Installing composer dependencies"
    if command -v composer >/dev/null 2>&1; then
        COMPOSER_MEMORY_LIMIT=-1 composer install \
            --no-dev --optimize-autoloader --no-interaction --no-progress
    else
        warn "composer not found on PATH; skipping"
    fi
fi

# ---------------------------------------------------------------------------
# 6. Frontend assets
# ---------------------------------------------------------------------------
if [ "$DO_BUILD" -eq 1 ]; then
    step "Building frontend assets"
    if command -v npm >/dev/null 2>&1; then
        if [ ! -d node_modules ] \
            || { [ -f package-lock.json ] && [ package-lock.json -nt node_modules/.package-lock.json ]; }; then
            log "npm install"
            npm ci --no-audit --no-fund || npm install --no-audit --no-fund
        else
            log "node_modules already up to date"
        fi
        npm run build
    else
        warn "npm not found; skipping build."
        warn "Build locally with 'npm run build' and upload public/build instead."
    fi
fi

# ---------------------------------------------------------------------------
# 7. Database migrations
# ---------------------------------------------------------------------------
if [ "$DO_MIGRATE" -eq 1 ]; then
    step "Running migrations"
    "$PHP_BIN" artisan migrate --force
else
    log "--> migrations skipped (--no-migrate)"
fi

# ---------------------------------------------------------------------------
# 8. Public storage symlink + permissions
# ---------------------------------------------------------------------------
step "Repairing storage link and permissions"
if [ -f scripts/fix-storage.sh ]; then
    PHP_BIN="$PHP_BIN" bash scripts/fix-storage.sh || warn "fix-storage reported problems; review the output above"
else
    "$PHP_BIN" artisan storage:link --force >/dev/null 2>&1 || true
    chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true
    log "storage:link + permissions applied"
fi

# ---------------------------------------------------------------------------
# 9. Recache config, routes, views, events
# ---------------------------------------------------------------------------
if [ "$DO_OPTIMIZE" -eq 1 ]; then
    step "Optimizing (config, routes, views, events)"
    "$PHP_BIN" artisan optimize
else
    log "--> caching skipped (--no-optimize)"
fi

# ---------------------------------------------------------------------------
# 10. Lift maintenance mode
# ---------------------------------------------------------------------------
step "Lifting maintenance mode"
if [ "$SITE_DOWN" -eq 1 ]; then
    if "$PHP_BIN" artisan up; then
        SITE_DOWN=0
    else
        warn "php artisan up failed; run it manually"
    fi
else
    log "site was never taken down"
fi

# ---------------------------------------------------------------------------
# Summary
# ---------------------------------------------------------------------------
step "Summary"
log "  commit:  $(git log --oneline -1 2>/dev/null || echo 'n/a')"
log "  app url: $(env_get APP_URL)"
"$PHP_BIN" artisan migrate:status 2>/dev/null | tail -n 2 || true
log ""
log "Redeploy finished."
