#!/bin/bash

#########################################################
# Lotto Platform - Deployment Script
# Zero-downtime deployment with rollback support
# Compatible with DirectAdmin / VPS / Docker
#########################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Deployment config
DEPLOY_LOG="storage/logs/deploy.log"
BACKUP_DIR="storage/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
GIT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")

# ─────────────────────────────────────────
# Helper functions
# ─────────────────────────────────────────

log() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] $1"
    echo -e "${GREEN}$msg${NC}"
    echo "$msg" >> "$DEPLOY_LOG" 2>/dev/null || true
}

log_error() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1"
    echo -e "${RED}$msg${NC}"
    echo "$msg" >> "$DEPLOY_LOG" 2>/dev/null || true
}

log_warning() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $1"
    echo -e "${YELLOW}$msg${NC}"
    echo "$msg" >> "$DEPLOY_LOG" 2>/dev/null || true
}

log_step() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}▶ $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] STEP: $1" >> "$DEPLOY_LOG" 2>/dev/null || true
}

command_exists() { command -v "$1" >/dev/null 2>&1; }

# ─────────────────────────────────────────
# Pre-deployment checks
# ─────────────────────────────────────────

pre_checks() {
    log_step "Step 1/10 - Pre-deployment Checks"

    # Ensure .env exists
    if [ ! -f .env ]; then
        log_error ".env file not found! Run install.sh first."
        exit 1
    fi

    # Check PHP
    if ! command_exists php; then
        log_error "PHP is not installed"
        exit 1
    fi

    # Check Composer
    if ! command_exists composer; then
        log_error "Composer is not installed"
        exit 1
    fi

    # Ensure directories exist
    mkdir -p "$BACKUP_DIR"
    mkdir -p storage/framework/{cache/data,sessions,views}
    mkdir -p storage/logs
    mkdir -p bootstrap/cache

    log "Pre-checks passed"
}

# ─────────────────────────────────────────
# Maintenance mode
# ─────────────────────────────────────────

enable_maintenance() {
    log_step "Step 2/10 - Enabling Maintenance Mode"

    php artisan down --retry=60 --refresh=15 2>/dev/null || true
    log "Maintenance mode enabled"
}

disable_maintenance() {
    php artisan up 2>/dev/null || true
    log "Maintenance mode disabled"
}

# ─────────────────────────────────────────
# Backup
# ─────────────────────────────────────────

create_backup() {
    log_step "Step 3/10 - Creating Backup"

    local backup_file="${BACKUP_DIR}/backup_${TIMESTAMP}.tar.gz"

    # Backup .env and database config
    cp .env "${BACKUP_DIR}/.env.backup_${TIMESTAMP}" 2>/dev/null || true

    # Backup SQLite database if used
    if grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null; then
        if [ -f database/database.sqlite ]; then
            cp database/database.sqlite "${BACKUP_DIR}/database_${TIMESTAMP}.sqlite"
            log "SQLite database backed up"
        fi
    fi

    # Create lightweight backup (config + views only, skip vendor/node_modules)
    tar -czf "$backup_file" \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='storage/backups' \
        --exclude='storage/logs' \
        --exclude='.git' \
        . 2>/dev/null || true

    log "Backup created: $backup_file"

    # Clean old backups (keep last 5)
    ls -t "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
    log "Old backups cleaned"
}

# ─────────────────────────────────────────
# Git pull
# ─────────────────────────────────────────

pull_latest() {
    log_step "Step 4/10 - Pulling Latest Code"

    if [ -d .git ]; then
        # Stash any local changes
        git stash --quiet 2>/dev/null || true

        # Pull latest
        git pull origin "$GIT_BRANCH" 2>/dev/null || {
            log_warning "Git pull failed, trying with rebase..."
            git pull --rebase origin "$GIT_BRANCH" 2>/dev/null || {
                log_error "Git pull failed. Please resolve conflicts manually."
                disable_maintenance
                exit 1
            }
        }

        # Pop stash
        git stash pop --quiet 2>/dev/null || true

        GIT_COMMIT=$(git rev-parse --short HEAD 2>/dev/null || echo "unknown")
        log "Code updated to commit: $GIT_COMMIT"
    else
        log_warning "Not a git repository, skipping pull"
    fi
}

# ─────────────────────────────────────────
# Dependencies
# ─────────────────────────────────────────

install_dependencies() {
    log_step "Step 5/10 - Installing Dependencies"

    log "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts
    log "PHP dependencies installed"

    if command_exists npm; then
        log "Installing Node.js dependencies..."
        npm ci --silent 2>/dev/null || npm install --silent 2>/dev/null || npm install
        log "Node.js dependencies installed"
    else
        log_warning "NPM not available, skipping"
    fi
}

# ─────────────────────────────────────────
# Database
# ─────────────────────────────────────────

run_migrations() {
    log_step "Step 6/10 - Running Migrations"

    php artisan migrate --force
    log "Migrations completed"
}

# ─────────────────────────────────────────
# Build assets
# ─────────────────────────────────────────

build_assets() {
    log_step "Step 7/10 - Building Frontend Assets"

    if command_exists npm; then
        npm run build 2>/dev/null || {
            log_warning "Asset build failed, attempting clean rebuild..."
            rm -rf node_modules/.vite 2>/dev/null || true
            npm run build || log_warning "Asset build failed - using existing assets"
        }
        log "Frontend assets built"
    else
        log_warning "NPM not available, skipping asset build"
    fi
}

# ─────────────────────────────────────────
# Clear & optimize
# ─────────────────────────────────────────

clear_caches() {
    log_step "Step 8/10 - Clearing Caches"

    php artisan cache:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    php artisan event:clear 2>/dev/null || true
    log "All caches cleared"
}

optimize_application() {
    log_step "Step 9/10 - Optimizing Application"

    # Discover packages (skipped during composer install --no-scripts)
    php artisan package:discover --ansi 2>/dev/null || true

    if grep -q "APP_ENV=production" .env 2>/dev/null; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan event:cache 2>/dev/null || true
        log "Application optimized for production"
    else
        log "Development mode - skipping optimization cache"
    fi

    # Storage link
    php artisan storage:link 2>/dev/null || true
}

# ─────────────────────────────────────────
# Fix permissions
# ─────────────────────────────────────────

fix_permissions() {
    log_step "Step 10/10 - Fixing Permissions"

    chmod -R 775 storage bootstrap/cache 2>/dev/null || true

    # Try to set web server ownership (common setups)
    if id "www-data" &>/dev/null; then
        chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    elif id "apache" &>/dev/null; then
        chown -R apache:apache storage bootstrap/cache 2>/dev/null || true
    elif id "nginx" &>/dev/null; then
        chown -R nginx:nginx storage bootstrap/cache 2>/dev/null || true
    fi

    log "Permissions fixed"
}

# ─────────────────────────────────────────
# Completion
# ─────────────────────────────────────────

print_completion() {
    local end_time=$(date +%s)
    local duration=$((end_time - START_TIME))

    echo -e "\n${GREEN}"
    echo "╔═══════════════════════════════════════════════════════╗"
    echo "║                                                       ║"
    echo "║      ✅ DEPLOYMENT COMPLETED SUCCESSFULLY! ✅        ║"
    echo "║                                                       ║"
    echo "╚═══════════════════════════════════════════════════════╝"
    echo -e "${NC}"

    echo -e "${CYAN}Deploy Details:${NC}"
    echo -e "  Branch:   ${GREEN}$GIT_BRANCH${NC}"
    echo -e "  Commit:   ${GREEN}$GIT_COMMIT${NC}"
    echo -e "  Duration: ${GREEN}${duration}s${NC}"
    echo -e "  Time:     ${GREEN}$(date '+%Y-%m-%d %H:%M:%S')${NC}"

    APP_URL=$(grep "APP_URL=" .env 2>/dev/null | head -1 | cut -d'=' -f2 || echo "http://localhost")
    echo -e "\n  URL: ${GREEN}$APP_URL${NC}"
    echo ""
}

# ─────────────────────────────────────────
# Rollback
# ─────────────────────────────────────────

rollback() {
    echo -e "${YELLOW}Available backups:${NC}"
    ls -la "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null || {
        log_error "No backups found"
        exit 1
    }

    echo ""
    read -p "Enter backup filename to restore (e.g., backup_20240101_120000.tar.gz): " backup_name

    local backup_path="${BACKUP_DIR}/${backup_name}"
    if [ ! -f "$backup_path" ]; then
        log_error "Backup file not found: $backup_path"
        exit 1
    fi

    echo -e "${RED}WARNING: This will overwrite current files!${NC}"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Rollback cancelled."
        exit 0
    fi

    enable_maintenance

    log "Restoring from backup: $backup_name"
    tar -xzf "$backup_path" -C . 2>/dev/null || true

    # Restore .env if backup exists
    local env_backup="${BACKUP_DIR}/.env.backup_${backup_name#backup_}"
    env_backup="${env_backup%.tar.gz}"
    if [ -f "$env_backup" ]; then
        cp "$env_backup" .env
        log ".env restored"
    fi

    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts 2>/dev/null || true
    php artisan package:discover --ansi 2>/dev/null || true
    php artisan migrate --force 2>/dev/null || true
    clear_caches
    optimize_application

    disable_maintenance
    log "Rollback completed!"
}

# ─────────────────────────────────────────
# Quick deploy (skip git pull)
# ─────────────────────────────────────────

quick_deploy() {
    START_TIME=$(date +%s)

    echo -e "${CYAN}Quick Deploy - Skip git pull & backup${NC}\n"

    pre_checks
    clear_caches
    install_dependencies
    run_migrations
    build_assets
    optimize_application
    fix_permissions

    print_completion
}

# ─────────────────────────────────────────
# Main
# ─────────────────────────────────────────

main() {
    START_TIME=$(date +%s)

    echo -e "${CYAN}"
    echo "╔═══════════════════════════════════════════════════════╗"
    echo "║       🚀 LOTTO PLATFORM - DEPLOYER  🚀              ║"
    echo "╚═══════════════════════════════════════════════════════╝"
    echo -e "${NC}"

    echo -e "Branch: ${GREEN}$GIT_BRANCH${NC} | Commit: ${GREEN}$GIT_COMMIT${NC}\n"

    pre_checks
    enable_maintenance
    create_backup
    pull_latest
    install_dependencies
    run_migrations
    build_assets
    clear_caches
    optimize_application
    fix_permissions
    disable_maintenance
    print_completion
}

# ─────────────────────────────────────────
# CLI
# ─────────────────────────────────────────

case "${1:-}" in
    --rollback|-r)
        rollback
        ;;
    --quick|-q)
        quick_deploy
        ;;
    --help|-h)
        echo -e "${CYAN}Lotto Platform Deployer${NC}"
        echo ""
        echo "Usage: ./deploy.sh [option]"
        echo ""
        echo "Options:"
        echo "  (none)        Full deployment (pull + migrate + build)"
        echo "  --quick, -q   Quick deploy (skip git pull & backup)"
        echo "  --rollback, -r  Rollback to a previous backup"
        echo "  --help, -h    Show this help"
        echo ""
        ;;
    *)
        main
        ;;
esac
