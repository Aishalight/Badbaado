#!/usr/bin/env bash
#
# Repair public file storage for a deployed Badbaado instance.
#
# Uploaded avatars, hospital logos and referral attachments are written to
# storage/app/<...> on the "public" disk but served over HTTP through the
# public/storage symlink. That symlink is gitignored, so a git-based deploy
# never creates it and every upload 404s even though the file is on disk.
#
# This script repairs the three things that cause that:
#
#   1. the missing public/storage symlink
#   2. storage/ not writable by the web server user
#   3. required directories missing from a fresh clone
#
# It is idempotent and never deletes uploads.
#
# Usage:
#   bash scripts/fix-storage.sh              repair (default)
#   bash scripts/fix-storage.sh --check      diagnose only, change nothing
#   WEB_USER=nginx bash scripts/fix-storage.sh
#
# Run it as the deploy user, or with sudo if the web server user differs.

set -euo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_ROOT"

CHECK_ONLY=0
for arg in "$@"; do
    case "$arg" in
        --check) CHECK_ONLY=1 ;;
        -h|--help) sed -n '2,25p' "${BASH_SOURCE[0]}" | sed 's/^# \{0,1\}//'; exit 0 ;;
        *) echo "Unknown option: $arg" >&2; exit 2 ;;
    esac
done

log()  { printf '%s\n' "$*"; }
warn() { printf 'WARN  %s\n' "$*" >&2; }
fail() { printf 'ERROR %s\n' "$*" >&2; }

# Pick the user the web server runs as: prefer one with live processes,
# otherwise the first known name that exists on this host.
detect_web_user() {
    if [ -n "${WEB_USER:-}" ]; then
        echo "$WEB_USER"
        return
    fi

    local candidate fallback=""
    for candidate in www-data apache nginx caddy httpd; do
        id -u "$candidate" >/dev/null 2>&1 || continue
        [ -z "$fallback" ] && fallback="$candidate"
        if pgrep -u "$candidate" >/dev/null 2>&1; then
            echo "$candidate"
            return
        fi
    done

    echo "$fallback"
}

WEB_USER="$(detect_web_user)"

log "==> Badbaado storage repair"
log "    app root: $APP_ROOT"
log "    web user: ${WEB_USER:-<could not detect>}"
log ""

# Run a command with elevated privileges only when we are not already root.
# -n keeps sudo from prompting: in a non-interactive deploy a password
# prompt would hang the script instead of failing.
SUDO=""
if [ "$(id -u)" -ne 0 ]; then
    if command -v sudo >/dev/null 2>&1; then
        SUDO="sudo -n"
        if ! $SUDO true 2>/dev/null; then
            warn "sudo needs a password here; run this script with sudo, or as $WEB_USER"
            SUDO=""
        fi
    else
        warn "not running as root and sudo is unavailable; skipping ownership fixes"
    fi
fi

DIRECTORIES=(
    "bootstrap/cache"
    "storage/app/public"
    "storage/app/public/avatars"
    "storage/app/public/hospital-logos"
    "storage/app/private"
    "storage/app/backups"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/testing"
    "storage/framework/views"
    "storage/logs"
)

have_artisan() {
    [ "$HAVE_PHP" -eq 1 ] && [ -f vendor/autoload.php ]
}

# The artisan steps below need both the php binary and installed
# dependencies. Detect them once so a missing toolchain is reported up front
# instead of failing partway through the repair.
HAVE_PHP=0
PHP_BIN="${PHP_BIN:-php}"
if command -v "$PHP_BIN" >/dev/null 2>&1; then
    HAVE_PHP=1
else
    warn "php was not found on PATH; skipping all artisan steps."
    warn "On most servers PHP runs via a versioned binary, e.g. 'php8.3'."
    warn "Re-run with PHP_BIN set, e.g. PHP_BIN=php8.3 bash scripts/fix-storage.sh"
fi

# ---------------------------------------------------------------------------
# 1. Create the directories a fresh clone is missing
# ---------------------------------------------------------------------------
if [ "$CHECK_ONLY" -eq 0 ]; then
    for dir in "${DIRECTORIES[@]}"; do
        if [ ! -d "$dir" ]; then
            mkdir -p "$dir"
            log "created  $dir"
        fi
    done
else
    for dir in "${DIRECTORIES[@]}"; do
        [ -d "$dir" ] || warn "missing   $dir"
    done
fi

# ---------------------------------------------------------------------------
# 2. Hand storage and bootstrap/cache to the web server user
# ---------------------------------------------------------------------------
if [ -n "$WEB_USER" ]; then
    if [ "$CHECK_ONLY" -eq 0 ]; then
        if [ -n "$SUDO" ]; then
            $SUDO chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache 2>/dev/null \
                && log "chown    storage bootstrap/cache -> $WEB_USER" \
                || warn "chown failed for $WEB_USER; is the group name correct?"
        fi
        chmod -R ug+rwX,o+rX storage bootstrap/cache 2>/dev/null || true
        log "chmod    storage bootstrap/cache -> ug+rwX"
    fi
else
    warn "could not detect a web server user; set WEB_USER to fix ownership"
fi

# ---------------------------------------------------------------------------
# 3. Restore the public/storage symlink
# ---------------------------------------------------------------------------
if [ -L public/storage ] && [ ! -d public/storage ]; then
    # Dangling symlink: the target does not exist on this host.
    warn "public/storage is a broken symlink; recreating"
    [ "$CHECK_ONLY" -eq 0 ] && rm -f public/storage
fi

if [ "$CHECK_ONLY" -eq 0 ]; then
    if have_artisan; then
        if link_output="$("$PHP_BIN" artisan storage:link --force 2>&1)"; then
            if printf '%s' "$link_output" | grep -qi "already exists"; then
                log "linked   public/storage already present; left in place"
            else
                log "linked   public/storage -> storage/app/public"
            fi
        else
            printf '%s\n' "$link_output" >&2
            fail "artisan storage:link failed"
        fi
    else
        warn "vendor/ is missing, so artisan storage:link was skipped."
        warn "Run 'composer install' first, or create the link manually:"
        warn "    ln -sfn \"\$APP_ROOT/storage/app/public\" public/storage"
    fi
fi

# ---------------------------------------------------------------------------
# 4. Clear stale caches so a cached disk config cannot mask these fixes
# ---------------------------------------------------------------------------
if [ "$CHECK_ONLY" -eq 0 ] && have_artisan; then
    "$PHP_BIN" artisan config:clear >/dev/null 2>&1 && log "cleared  config cache" || true
    "$PHP_BIN" artisan view:clear >/dev/null 2>&1 && log "cleared  view cache" || true
fi

# ---------------------------------------------------------------------------
# Report
# ---------------------------------------------------------------------------
log ""
log "==> Verification"

if [ -L public/storage ]; then
    log "    symlink      public/storage -> $(readlink public/storage)"
elif [ -d public/storage ]; then
    warn "    symlink      public/storage is a real directory, not a symlink."
    warn "                  Move it aside; a real directory shadows later uploads."
else
    fail "    symlink      public/storage is missing"
fi

if [ -d public/storage ]; then
    # Confirm the link resolves to THIS project's storage dir, not a stale
    # target left over from an earlier deploy path.
    link_real="$(cd public/storage 2>/dev/null && pwd -P || echo '')"
    target_real="$(cd storage/app/public 2>/dev/null && pwd -P || echo '')"

    if [ -z "$target_real" ]; then
        fail "    target       storage/app/public does not exist on this host"
    elif [ -z "$link_real" ]; then
        fail "    target       public/storage could not be resolved"
    elif [ "$link_real" = "$target_real" ]; then
        log "    target       $target_real (matches storage/app/public)"
    else
        fail "    target       resolves to $link_real, expected $target_real"
        warn "                  stale link; remove it and re-run this script"
    fi
else
    fail "    target       does not resolve (check the symlink target path)"
fi

if [ -n "$WEB_USER" ] && [ -d storage/app/public ]; then
    if [ -n "$SUDO" ]; then
        # Always test as the web server user; root can write anywhere, so a
        # root-side check would report success even when uploads would fail.
        if $SUDO -u "$WEB_USER" test -w storage/app/public; then
            log "    writable     storage/app/public as $WEB_USER"
        else
            fail "    writable     storage/app/public NOT writable by $WEB_USER"
        fi
    elif [ "$(id -un)" = "$WEB_USER" ]; then
        if [ -w storage/app/public ]; then
            log "    writable     storage/app/public as $WEB_USER"
        else
            fail "    writable     storage/app/public NOT writable by $WEB_USER"
        fi
    else
        warn "    writable     could not verify as $WEB_USER (no sudo); check manually"
    fi
fi

if [ -f .env ]; then
    app_url="$(grep -E '^APP_URL=' .env | head -n1 | cut -d= -f2- || true)"
    if [ -n "$app_url" ]; then
        log "    APP_URL      $app_url"
        case "$app_url" in
            http://*) warn "    APP_URL is http; the public disk builds URLs from it," \
                           "so confirm that host matches where you browse." ;;
            https://*) log "    APP_URL is https; cookies are secure and URLs will match." ;;
        esac
    fi
else
    warn "    .env not found; cannot verify APP_URL"
fi

log ""
if [ "$CHECK_ONLY" -eq 1 ]; then
    log "Check complete. Re-run without --check to apply any fixes."
else
    log "Done. Upload an avatar or logo and confirm it loads."
fi
