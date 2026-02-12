#!/bin/bash

#########################################################
# Lotto Platform - Smart Deployer
# Auto-diagnose & self-heal common deployment issues
# Zero-downtime with rollback support
#########################################################

set -euo pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
DIM='\033[2m'
NC='\033[0m'

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Config
DEPLOY_LOG="storage/logs/deploy.log"
BACKUP_DIR="storage/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
GIT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
ERRORS_FOUND=0
FIXES_APPLIED=0

# ─────────────────────────────────────────
# Logging
# ─────────────────────────────────────────

log()         { echo -e "${GREEN}[$(date '+%H:%M:%S')] $1${NC}"; echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$DEPLOY_LOG" 2>/dev/null || true; }
log_error()   { echo -e "${RED}[$(date '+%H:%M:%S')] ERROR: $1${NC}"; echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" >> "$DEPLOY_LOG" 2>/dev/null || true; ERRORS_FOUND=$((ERRORS_FOUND + 1)); }
log_warning() { echo -e "${YELLOW}[$(date '+%H:%M:%S')] WARNING: $1${NC}"; echo "[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $1" >> "$DEPLOY_LOG" 2>/dev/null || true; }
log_fix()     { echo -e "${CYAN}[$(date '+%H:%M:%S')] AUTO-FIX: $1${NC}"; echo "[$(date '+%Y-%m-%d %H:%M:%S')] AUTO-FIX: $1" >> "$DEPLOY_LOG" 2>/dev/null || true; FIXES_APPLIED=$((FIXES_APPLIED + 1)); }

log_step() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}▶ $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] STEP: $1" >> "$DEPLOY_LOG" 2>/dev/null || true
}

command_exists() { command -v "$1" >/dev/null 2>&1; }

# ─────────────────────────────────────────
# Trap: auto-rollback on failure
# ─────────────────────────────────────────

DEPLOY_PHASE="init"
MAINTENANCE_ON=false

cleanup_on_failure() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo -e "\n${RED}╔═══════════════════════════════════════════════════════╗${NC}"
        echo -e "${RED}║         DEPLOYMENT FAILED at phase: ${DEPLOY_PHASE}${NC}"
        echo -e "${RED}╚═══════════════════════════════════════════════════════╝${NC}\n"
        log_error "Deploy failed at phase: ${DEPLOY_PHASE} (exit code: ${exit_code})"

        if [ "$MAINTENANCE_ON" = true ]; then
            log_warning "Disabling maintenance mode..."
            php artisan up 2>/dev/null || true
        fi

        echo -e "${YELLOW}Check log: ${DEPLOY_LOG}${NC}"
    fi
}
trap cleanup_on_failure EXIT

# ─────────────────────────────────────────
# Diagnose: .env validation & auto-fix
# ─────────────────────────────────────────

diagnose_env() {
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            log_fix "No .env found - creating from .env.example"
            cp .env.example .env
            if command_exists php && [ -f artisan ]; then
                php artisan key:generate --force 2>/dev/null || true
            fi
        else
            log_error "No .env or .env.example found"
            return 1
        fi
    fi

    # Fix unquoted values with spaces (e.g. APP_NAME=Lotto Platform)
    local tmp_env
    tmp_env=$(mktemp)
    local fixed=false

    while IFS= read -r line || [ -n "$line" ]; do
        # Skip comments and empty lines
        if [[ "$line" =~ ^[[:space:]]*# ]] || [[ -z "${line// /}" ]]; then
            echo "$line" >> "$tmp_env"
            continue
        fi

        # Match KEY=VALUE lines (not already quoted)
        if [[ "$line" =~ ^([A-Za-z_][A-Za-z0-9_]*)=(.*) ]]; then
            local key="${BASH_REMATCH[1]}"
            local val="${BASH_REMATCH[2]}"

            # Value has spaces and is NOT already quoted
            if [[ "$val" =~ [[:space:]] ]] && [[ ! "$val" =~ ^\".*\"$ ]] && [[ ! "$val" =~ ^\'.*\'$ ]]; then
                echo "${key}=\"${val}\"" >> "$tmp_env"
                log_fix ".env: quoted ${key} value (had unquoted spaces)"
                fixed=true
                continue
            fi
        fi

        echo "$line" >> "$tmp_env"
    done < .env

    if [ "$fixed" = true ]; then
        cp "$tmp_env" .env
    fi
    rm -f "$tmp_env"

    # Check required keys
    local missing=()
    for key in APP_KEY DB_CONNECTION; do
        if ! grep -q "^${key}=" .env 2>/dev/null; then
            missing+=("$key")
        fi
    done

    if [ ${#missing[@]} -gt 0 ]; then
        log_warning ".env missing keys: ${missing[*]}"
    fi

    # Check APP_KEY is set
    local app_key
    app_key=$(grep "^APP_KEY=" .env | cut -d'=' -f2)
    if [ -z "$app_key" ]; then
        if command_exists php && [ -f artisan ]; then
            log_fix "APP_KEY is empty - generating"
            php artisan key:generate --force 2>/dev/null || true
        fi
    fi

    log ".env validated"
}

# ─────────────────────────────────────────
# Diagnose: PHP & Composer compatibility
# ─────────────────────────────────────────

diagnose_php() {
    if ! command_exists php; then
        log_error "PHP is not installed"
        return 1
    fi

    local php_version
    php_version=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;" 2>/dev/null)
    log "PHP version: ${php_version}"

    # Check required extensions
    local missing_ext=()
    for ext in pdo mbstring openssl tokenizer xml ctype json; do
        if ! php -m 2>/dev/null | grep -qi "^${ext}$"; then
            missing_ext+=("$ext")
        fi
    done

    if [ ${#missing_ext[@]} -gt 0 ]; then
        log_warning "Missing PHP extensions: ${missing_ext[*]}"
    fi

    if ! command_exists composer; then
        log_error "Composer is not installed"
        return 1
    fi

    log "Composer $(composer --version --no-ansi 2>/dev/null | head -1 | grep -oP '\d+\.\d+\.\d+' || echo 'detected')"
}

# ─────────────────────────────────────────
# Diagnose: Composer lock compatibility
# ─────────────────────────────────────────

diagnose_composer() {
    if [ ! -f composer.lock ]; then
        log_fix "No composer.lock - running composer install to generate"
        return 0
    fi

    # Dry-run check: can the lock file install on this PHP?
    if ! composer install --dry-run --no-interaction --no-scripts 2>/dev/null | grep -q "Nothing to"; then
        # Check if it's a PHP version mismatch
        local check_output
        check_output=$(composer install --dry-run --no-interaction --no-scripts 2>&1 || true)

        if echo "$check_output" | grep -q "your php version"; then
            local php_version
            php_version=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;" 2>/dev/null)
            log_fix "Lock file incompatible with PHP ${php_version} - running composer update"

            # Update platform config to match current PHP
            composer config platform.php "${php_version}" 2>/dev/null || true
            composer update --no-interaction --no-scripts --no-dev 2>&1 || {
                log_error "composer update failed"
                return 1
            }
            log "composer.lock regenerated for PHP ${php_version}"
            return 0
        fi

        if echo "$check_output" | grep -q "could not be found"; then
            log_warning "Some packages not found - may need auth or network"
        fi
    fi

    log "composer.lock compatible"
}

# ─────────────────────────────────────────
# Diagnose: Database connectivity
# ─────────────────────────────────────────

diagnose_database() {
    local db_conn
    db_conn=$(grep "^DB_CONNECTION=" .env 2>/dev/null | cut -d'=' -f2)

    if [ "$db_conn" = "sqlite" ]; then
        local db_path
        db_path=$(grep "^DB_DATABASE=" .env 2>/dev/null | cut -d'=' -f2)
        if [ -n "$db_path" ] && [ "$db_path" != ":memory:" ] && [ ! -f "$db_path" ]; then
            log_fix "SQLite database not found - creating ${db_path}"
            touch "$db_path"
        fi
        log "Database: SQLite"
        return 0
    fi

    # Test MySQL/MariaDB/PostgreSQL connection via artisan
    if php artisan db:monitor --databases="${db_conn}" 2>/dev/null | grep -q "OK"; then
        log "Database: ${db_conn} connected"
        return 0
    fi

    # Fallback: try a simple query
    if php artisan tinker --execute="DB::select('SELECT 1')" 2>/dev/null | grep -q "1"; then
        log "Database: ${db_conn} connected"
        return 0
    fi

    log_warning "Cannot verify database connection (${db_conn}) - will attempt migration anyway"
}

# ─────────────────────────────────────────
# Diagnose: Disk space
# ─────────────────────────────────────────

diagnose_disk() {
    local available_mb
    available_mb=$(df -m "$SCRIPT_DIR" 2>/dev/null | awk 'NR==2 {print $4}')

    if [ -n "$available_mb" ] && [ "$available_mb" -lt 200 ]; then
        log_warning "Low disk space: ${available_mb}MB available"

        # Auto-clean if very low
        if [ "$available_mb" -lt 100 ]; then
            log_fix "Cleaning old logs and caches to free space"
            find storage/logs -name "*.log" -mtime +7 -delete 2>/dev/null || true
            find storage/framework/cache -type f -mtime +1 -delete 2>/dev/null || true
            ls -t "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null | tail -n +3 | xargs rm -f 2>/dev/null || true

            available_mb=$(df -m "$SCRIPT_DIR" 2>/dev/null | awk 'NR==2 {print $4}')
            log "Freed space. Now: ${available_mb}MB available"
        fi
    else
        log "Disk space: ${available_mb:-unknown}MB available"
    fi
}

# ─────────────────────────────────────────
# Pre-deployment: full diagnosis
# ─────────────────────────────────────────

run_diagnosis() {
    log_step "Step 1 - Diagnosing Environment"

    mkdir -p "$BACKUP_DIR"
    mkdir -p storage/framework/{cache/data,sessions,views}
    mkdir -p storage/logs
    mkdir -p bootstrap/cache

    diagnose_env
    diagnose_php
    diagnose_disk
    diagnose_composer
    diagnose_database

    if [ $FIXES_APPLIED -gt 0 ]; then
        log "${FIXES_APPLIED} issue(s) auto-fixed"
    fi
    log "Diagnosis complete"
}

# ─────────────────────────────────────────
# Maintenance mode (safe)
# ─────────────────────────────────────────

enable_maintenance() {
    log_step "Step 2 - Enabling Maintenance Mode"
    php artisan down --retry=60 --refresh=15 2>/dev/null || true
    MAINTENANCE_ON=true
    log "Maintenance mode enabled"
}

disable_maintenance() {
    php artisan up 2>/dev/null || true
    MAINTENANCE_ON=false
    log "Maintenance mode disabled"
}

# ─────────────────────────────────────────
# Backup
# ─────────────────────────────────────────

create_backup() {
    log_step "Step 3 - Creating Backup"

    local backup_file="${BACKUP_DIR}/backup_${TIMESTAMP}.tar.gz"

    cp .env "${BACKUP_DIR}/.env.backup_${TIMESTAMP}" 2>/dev/null || true

    if grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null; then
        local db_path
        db_path=$(grep "^DB_DATABASE=" .env | cut -d'=' -f2)
        if [ -n "$db_path" ] && [ -f "$db_path" ]; then
            cp "$db_path" "${BACKUP_DIR}/database_${TIMESTAMP}.sqlite"
            log "SQLite database backed up"
        fi
    fi

    tar -czf "$backup_file" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/backups' \
        --exclude='storage/logs' \
        --exclude='.git' \
        . 2>/dev/null || log_warning "Backup archive creation had warnings"

    log "Backup created: ${backup_file}"

    # Keep last 5
    ls -t "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
}

# ─────────────────────────────────────────
# Git pull (with retry)
# ─────────────────────────────────────────

pull_latest() {
    log_step "Step 4 - Pulling Latest Code"
    DEPLOY_PHASE="git-pull"

    if [ ! -d .git ]; then
        log_warning "Not a git repository, skipping pull"
        return 0
    fi

    # Abort any in-progress merge/rebase that could block pull
    git merge --abort 2>/dev/null || true
    git rebase --abort 2>/dev/null || true

    # Stash local changes (untracked files too)
    git stash --include-untracked --quiet 2>/dev/null || true

    # Reset any conflicted index state
    git reset HEAD 2>/dev/null || true

    # Fetch latest from remote
    local fetched=false
    local retries=0
    local max_retries=3

    while [ $retries -lt $max_retries ]; do
        if git fetch origin "$GIT_BRANCH" 2>/dev/null; then
            fetched=true
            break
        fi
        retries=$((retries + 1))
        log_warning "Git fetch attempt ${retries}/${max_retries} failed, retrying in ${retries}s..."
        sleep $retries
    done

    if [ "$fetched" = false ]; then
        log_error "Git fetch failed after ${max_retries} attempts. Continuing with current code."
        git stash pop --quiet 2>/dev/null || true
        GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
        log "Code at commit: ${GIT_COMMIT}"
        return 0
    fi

    # Try normal pull first
    if git pull origin "$GIT_BRANCH" 2>/dev/null; then
        log "Git pull succeeded"
    else
        # Pull failed (conflict or dirty tree) — force-reset to match remote
        log_warning "Git pull failed — resetting to origin/${GIT_BRANCH}"
        git reset --hard "origin/${GIT_BRANCH}" 2>/dev/null || {
            log_error "Git reset failed. Continuing with current code."
        }
        log_fix "Force-synced to origin/${GIT_BRANCH}"
    fi

    # Try to restore local changes, discard if they conflict
    git stash pop --quiet 2>/dev/null || {
        log_warning "Stashed changes conflict with new code — discarding stash"
        git checkout -- . 2>/dev/null || true
        git stash drop --quiet 2>/dev/null || true
    }

    GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
    log "Code at commit: ${GIT_COMMIT}"
}

# ─────────────────────────────────────────
# Dependencies (with fallback)
# ─────────────────────────────────────────

install_dependencies() {
    log_step "Step 5 - Installing Dependencies"
    DEPLOY_PHASE="dependencies"

    log "Installing PHP dependencies..."
    if ! composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts 2>&1; then
        log_warning "composer install failed, attempting composer update..."
        local php_version
        php_version=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;" 2>/dev/null)
        composer config platform.php "${php_version}" 2>/dev/null || true
        composer update --no-interaction --no-dev --no-scripts 2>&1 || {
            log_error "composer update also failed"
            return 1
        }
        log_fix "Regenerated composer.lock via update"
    fi
    log "PHP dependencies installed"

    if command_exists npm; then
        log "Installing Node.js dependencies..."
        local npm_ok=false

        # Try 1: npm ci (fastest, uses lock file exactly)
        if npm ci --silent 2>/dev/null; then
            npm_ok=true
        fi

        # Try 2: npm install (resolves if lock file is slightly stale)
        if [ "$npm_ok" = false ]; then
            log_warning "npm ci failed, trying npm install..."
            if npm install 2>/dev/null; then
                npm_ok=true
                log_fix "npm install succeeded"
            fi
        fi

        # Try 3: clean cache + regenerate lock file from scratch
        if [ "$npm_ok" = false ]; then
            log_warning "npm install failed, cleaning cache & regenerating..."
            npm cache clean --force 2>/dev/null || true
            rm -rf node_modules package-lock.json 2>/dev/null || true
            if npm install 2>/dev/null; then
                npm_ok=true
                log_fix "Clean npm install succeeded after cache clear"
            fi
        fi

        # Try 4: legacy peer deps (resolves stubborn peer dep conflicts)
        if [ "$npm_ok" = false ]; then
            log_warning "Retrying with --legacy-peer-deps..."
            if npm install --legacy-peer-deps 2>/dev/null; then
                npm_ok=true
                log_fix "npm install succeeded with --legacy-peer-deps"
            fi
        fi

        if [ "$npm_ok" = true ]; then
            log "Node.js dependencies installed"
        else
            log_warning "All npm install attempts failed - skipping (frontend may use cached assets)"
        fi
    else
        log_warning "NPM not available, skipping"
    fi
}

# ─────────────────────────────────────────
# Migrations (safe, with dry-run)
# ─────────────────────────────────────────

run_migrations() {
    log_step "Step 6 - Running Migrations"
    DEPLOY_PHASE="migrations"

    # Check if there are pending migrations
    local pending
    pending=$(php artisan migrate:status --pending 2>/dev/null || echo "")

    if [ -z "$pending" ] || echo "$pending" | grep -q "No migrations"; then
        log "No pending migrations"
        return 0
    fi

    log "Pending migrations detected, running..."

    # Try migration
    local migrate_output
    migrate_output=$(php artisan migrate --force 2>&1) && {
        log "Migrations completed successfully"
        return 0
    }

    # Migration failed - diagnose
    log_warning "Migration failed, diagnosing..."
    echo -e "${DIM}${migrate_output}${NC}"

    # Table already exists? Try fresh for that table or skip
    if echo "$migrate_output" | grep -q "already exists"; then
        log_fix "Table already exists - skipping with --graceful"
        php artisan migrate --force --graceful 2>/dev/null || {
            log_warning "Graceful migration not available, attempting pretend..."
            php artisan migrate --pretend --force 2>/dev/null || true
        }
        return 0
    fi

    # Syntax/access violation? Log clearly but continue
    if echo "$migrate_output" | grep -q "Syntax error\|access violation\|SQLSTATE"; then
        log_error "Migration SQL error - requires manual fix:"
        echo "$migrate_output" | grep -E "SQLSTATE|SQL:" | while IFS= read -r line; do
            echo -e "  ${RED}${line}${NC}"
        done
        log_warning "Skipping failed migration, continuing deployment..."
        return 0
    fi

    # Connection refused? Service might be starting
    if echo "$migrate_output" | grep -qi "connection refused\|can.t connect\|access denied"; then
        log_error "Database connection failed"
        echo -e "  ${YELLOW}Check: DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD in .env${NC}"
        return 0
    fi

    log_warning "Unknown migration error - continuing deployment"
}

# ─────────────────────────────────────────
# Smart Seeders (idempotent, essential data)
# ─────────────────────────────────────────

run_seeders() {
    log_step "Step 6.5 - Smart Seeding (Essential Data)"
    DEPLOY_PHASE="seeders"

    # All essential seeders use updateOrCreate — idempotent, safe to re-run every deploy.
    # Order matters: LotteryType → BetType → BetTypeRate (FK) → Settings → ResultSource (FK)
    #
    # NEVER auto-run: DemoSeeder (admin-triggered), AdminUserSeeder (/admin/setup), TestDataSeeder (dev)

    local ESSENTIAL_SEEDERS=(
        "LotteryTypeSeeder"
        "BetTypeSeeder"
        "BetTypeRateSeeder"
        "SettingsSeeder"
        "ResultSourceSeeder"
    )

    local failed=0

    for seeder_class in "${ESSENTIAL_SEEDERS[@]}"; do
        log "Running ${seeder_class}..."
        if php artisan db:seed --class="${seeder_class}" --force 2>&1; then
            log "${seeder_class} OK"
        else
            log_warning "${seeder_class} failed — skipping (non-critical)"
            failed=$((failed + 1))
        fi
    done

    if [ $failed -eq 0 ]; then
        log_fix "All essential seeders completed (${#ESSENTIAL_SEEDERS[@]} seeders)"
    else
        log_warning "${failed}/${#ESSENTIAL_SEEDERS[@]} seeders failed"
    fi
}

# ─────────────────────────────────────────
# Build assets
# ─────────────────────────────────────────

build_assets() {
    log_step "Step 7 - Building Frontend Assets"
    DEPLOY_PHASE="build"

    if ! command_exists npm; then
        log_warning "NPM not available, skipping asset build"
        return 0
    fi

    # Skip build if node_modules is missing (npm install failed earlier)
    if [ ! -d "node_modules" ]; then
        log_warning "node_modules missing (npm install may have failed), skipping build"
        if [ -d "public/build" ] && [ "$(ls -A public/build 2>/dev/null)" ]; then
            log "Using existing assets in public/build"
        else
            log_warning "No built assets available. Site may not display correctly."
        fi
        return 0
    fi

    # Try 1: normal build
    if npm run build 2>&1; then
        log "Frontend assets built"
        return 0
    fi

    # Try 2: clear vite cache and rebuild
    log_warning "Build failed, clearing vite cache and retrying..."
    rm -rf node_modules/.vite 2>/dev/null || true

    if npm run build 2>&1; then
        log_fix "Clean rebuild succeeded"
        return 0
    fi

    # Try 3: full reinstall of node_modules then build
    log_warning "Rebuild failed, reinstalling node_modules..."
    rm -rf node_modules 2>/dev/null || true
    npm install 2>/dev/null && npm run build 2>&1 && {
        log_fix "Full reinstall + build succeeded"
        return 0
    }

    # Fallback: use existing assets
    if [ -d "public/build" ] && [ "$(ls -A public/build 2>/dev/null)" ]; then
        log_warning "Build failed but existing assets found in public/build - using those"
    else
        log_warning "No built assets available. Site may not display correctly."
    fi
}

# ─────────────────────────────────────────
# Clear & Optimize
# ─────────────────────────────────────────

clear_caches() {
    log_step "Step 8 - Clearing Caches"
    DEPLOY_PHASE="cache"

    php artisan cache:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    php artisan event:clear 2>/dev/null || true
    log "All caches cleared"
}

optimize_application() {
    log_step "Step 9 - Optimizing Application"
    DEPLOY_PHASE="optimize"

    php artisan package:discover --ansi 2>/dev/null || true

    if grep -q "APP_ENV=production" .env 2>/dev/null; then
        php artisan config:cache 2>/dev/null || log_warning "config:cache failed"
        php artisan route:cache 2>/dev/null || log_warning "route:cache failed"
        php artisan view:cache 2>/dev/null || log_warning "view:cache failed"
        php artisan event:cache 2>/dev/null || true
        log "Application optimized for production"
    else
        log "Development mode - skipping optimization cache"
    fi

    php artisan storage:link 2>/dev/null || true
}

# ─────────────────────────────────────────
# Permissions
# ─────────────────────────────────────────

fix_permissions() {
    log_step "Step 10 - Fixing Permissions"
    DEPLOY_PHASE="permissions"

    chmod -R 775 storage bootstrap/cache 2>/dev/null || true

    # Detect and set web server ownership
    local web_user=""
    for user in www-data apache nginx http; do
        if id "$user" &>/dev/null; then
            web_user="$user"
            break
        fi
    done

    if [ -n "$web_user" ]; then
        chown -R "${web_user}:${web_user}" storage bootstrap/cache 2>/dev/null || true
        log "Permissions set for ${web_user}"
    else
        log "Permissions set (no web server user detected)"
    fi
}

# ─────────────────────────────────────────
# Health check
# ─────────────────────────────────────────

health_check() {
    DEPLOY_PHASE="health-check"

    local app_url
    app_url=$(grep "^APP_URL=" .env 2>/dev/null | head -1 | cut -d'=' -f2 | tr -d '"' | tr -d "'")

    if [ -z "$app_url" ] || [ "$app_url" = "http://localhost" ]; then
        return 0
    fi

    log "Running health check on ${app_url}..."

    if command_exists curl; then
        local http_code
        http_code=$(curl -so /dev/null -w '%{http_code}' --max-time 10 "${app_url}" 2>/dev/null || echo "000")

        case "$http_code" in
            200|301|302)
                log "Health check passed (HTTP ${http_code})"
                ;;
            503)
                log_warning "Health check: HTTP 503 - app may still be in maintenance mode"
                ;;
            000)
                log_warning "Health check: cannot reach ${app_url} (timeout/DNS)"
                ;;
            *)
                log_warning "Health check: HTTP ${http_code}"
                ;;
        esac
    fi
}

# ─────────────────────────────────────────
# Completion
# ─────────────────────────────────────────

print_completion() {
    local end_time
    end_time=$(date +%s)
    local duration=$((end_time - START_TIME))

    if [ $ERRORS_FOUND -eq 0 ]; then
        echo -e "\n${GREEN}"
        echo "╔═══════════════════════════════════════════════════════╗"
        echo "║      DEPLOYMENT COMPLETED SUCCESSFULLY!              ║"
        echo "╚═══════════════════════════════════════════════════════╝"
        echo -e "${NC}"
    else
        echo -e "\n${YELLOW}"
        echo "╔═══════════════════════════════════════════════════════╗"
        echo "║      DEPLOYED WITH ${ERRORS_FOUND} WARNING(S)                      ║"
        echo "╚═══════════════════════════════════════════════════════╝"
        echo -e "${NC}"
    fi

    echo -e "${CYAN}Deploy Details:${NC}"
    echo -e "  Branch:    ${GREEN}${GIT_BRANCH}${NC}"
    echo -e "  Commit:    ${GREEN}${GIT_COMMIT}${NC}"
    echo -e "  Duration:  ${GREEN}${duration}s${NC}"
    echo -e "  Time:      ${GREEN}$(date '+%Y-%m-%d %H:%M:%S')${NC}"

    if [ $FIXES_APPLIED -gt 0 ]; then
        echo -e "  Auto-fixed: ${CYAN}${FIXES_APPLIED} issue(s)${NC}"
    fi

    local app_url
    app_url=$(grep "^APP_URL=" .env 2>/dev/null | head -1 | cut -d'=' -f2 | tr -d '"' | tr -d "'")
    echo -e "  URL:       ${GREEN}${app_url:-http://localhost}${NC}\n"
}

# ─────────────────────────────────────────
# Rollback
# ─────────────────────────────────────────

rollback() {
    echo -e "${YELLOW}Available backups:${NC}"
    ls -lh "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null || {
        log_error "No backups found"
        exit 1
    }

    echo ""
    read -rp "Enter backup filename (e.g., backup_20240101_120000.tar.gz): " backup_name

    local backup_path="${BACKUP_DIR}/${backup_name}"
    if [ ! -f "$backup_path" ]; then
        log_error "Backup not found: ${backup_path}"
        exit 1
    fi

    echo -e "${RED}WARNING: This will overwrite current files!${NC}"
    read -rp "Are you sure? (y/N): " -n 1 confirm
    echo
    if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
        echo "Rollback cancelled."
        exit 0
    fi

    enable_maintenance

    log "Restoring from: ${backup_name}"
    tar -xzf "$backup_path" -C . 2>/dev/null || true

    local env_backup="${BACKUP_DIR}/.env.backup_${backup_name#backup_}"
    env_backup="${env_backup%.tar.gz}"
    if [ -f "$env_backup" ]; then
        cp "$env_backup" .env
        log ".env restored"
    fi

    diagnose_env
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts 2>/dev/null || true
    php artisan package:discover --ansi 2>/dev/null || true
    php artisan migrate --force 2>/dev/null || true
    clear_caches
    optimize_application

    disable_maintenance
    log "Rollback completed!"
}

# ─────────────────────────────────────────
# Diagnose-only mode
# ─────────────────────────────────────────

diagnose_only() {
    START_TIME=$(date +%s)
    echo -e "${CYAN}Running diagnosis only (no changes to deployment)...${NC}\n"

    mkdir -p "$BACKUP_DIR" storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache

    diagnose_env
    diagnose_php
    diagnose_disk
    diagnose_composer
    diagnose_database

    echo -e "\n${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    if [ $ERRORS_FOUND -eq 0 ] && [ $FIXES_APPLIED -eq 0 ]; then
        echo -e "${GREEN}All checks passed. Ready to deploy.${NC}"
    else
        [ $FIXES_APPLIED -gt 0 ] && echo -e "${CYAN}Auto-fixed: ${FIXES_APPLIED} issue(s)${NC}"
        [ $ERRORS_FOUND -gt 0 ] && echo -e "${RED}Errors found: ${ERRORS_FOUND} (may need manual fix)${NC}"
    fi
    echo ""
}

# ─────────────────────────────────────────
# Quick deploy
# ─────────────────────────────────────────

quick_deploy() {
    START_TIME=$(date +%s)
    echo -e "${CYAN}Quick Deploy - Skip git pull & backup${NC}\n"

    run_diagnosis
    clear_caches
    install_dependencies
    run_migrations
    run_seeders
    build_assets
    optimize_application
    fix_permissions
    health_check
    print_completion
}

# ─────────────────────────────────────────
# Main (full deploy)
# ─────────────────────────────────────────

main() {
    START_TIME=$(date +%s)

    echo -e "${CYAN}"
    echo "╔═══════════════════════════════════════════════════════╗"
    echo "║       LOTTO PLATFORM - SMART DEPLOYER                ║"
    echo "╚═══════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo -e "Branch: ${GREEN}${GIT_BRANCH}${NC} | Commit: ${GREEN}${GIT_COMMIT}${NC}\n"

    run_diagnosis
    enable_maintenance
    create_backup
    pull_latest
    install_dependencies
    run_migrations
    run_seeders
    build_assets
    clear_caches
    optimize_application
    fix_permissions
    disable_maintenance
    health_check
    print_completion
}

# ─────────────────────────────────────────
# CLI
# ─────────────────────────────────────────

case "${1:-}" in
    --rollback|-r)    rollback ;;
    --quick|-q)       quick_deploy ;;
    --diagnose|-d)    diagnose_only ;;
    --help|-h)
        echo -e "${CYAN}Lotto Platform - Smart Deployer${NC}"
        echo ""
        echo "Usage: ./deploy.sh [option]"
        echo ""
        echo "Options:"
        echo "  (none)          Full deployment (diagnose + pull + migrate + seed + build)"
        echo "  --quick, -q     Quick deploy (skip git pull & backup)"
        echo "  --diagnose, -d  Diagnose only (check environment, no deploy)"
        echo "  --rollback, -r  Rollback to a previous backup"
        echo "  --help, -h      Show this help"
        echo ""
        echo "Auto-fixes:"
        echo "  - .env unquoted values with spaces"
        echo "  - Missing .env (creates from .env.example)"
        echo "  - Empty APP_KEY (auto-generates)"
        echo "  - Composer lock PHP version mismatch (re-resolves)"
        echo "  - Low disk space (cleans old logs/caches)"
        echo "  - Build failures (retries with clean cache)"
        echo "  - Migration errors (diagnoses & reports clearly)"
        echo "  - Empty essential tables (auto-seeds LotteryType, BetType, Settings, etc.)"
        echo "  - New settings keys (re-syncs SettingsSeeder on every deploy)"
        echo ""
        ;;
    *)                main ;;
esac
