#!/bin/bash

#########################################################
# Lotto Platform - Installation Wizard
# Interactive installation with admin setup
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

# ─────────────────────────────────────────
# Helper functions
# ─────────────────────────────────────────

print_logo() {
    echo -e "${CYAN}"
    echo "╔═══════════════════════════════════════════════════════╗"
    echo "║                                                       ║"
    echo "║       🎰 LOTTO PLATFORM - INSTALLER  🎰              ║"
    echo "║                                                       ║"
    echo "║           Modern Online Lottery System                ║"
    echo "║       Laravel 12 + Tailwind CSS + Alpine.js          ║"
    echo "║                                                       ║"
    echo "╚═══════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

print_step() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${CYAN}▶ $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error()   { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info()    { echo -e "${PURPLE}ℹ $1${NC}"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

get_input() {
    local prompt="$1" default="$2" value
    if [ -n "$default" ]; then
        read -p "$prompt [$default]: " value
        echo "${value:-$default}"
    else
        read -p "$prompt: " value
        echo "$value"
    fi
}

get_password() {
    local prompt="$1" password
    read -s -p "$prompt: " password
    echo ""
    echo "$password"
}

update_env_var() {
    local key="$1" value="$2" env_file=".env"
    if [[ "$value" =~ [[:space:]\$\"\'\`\\!\#\&\|\;\<\>\(\)\[\]\{\}\*\?\~\@] ]]; then
        value="${value//\\/\\\\}"
        value="${value//\"/\\\"}"
        value="${value//\$/\\\$}"
        value="${value//\`/\\\`}"
        value="\"$value\""
    fi
    local new_line="${key}=${value}"
    if grep -q "^${key}=" "$env_file" 2>/dev/null; then
        grep -v "^${key}=" "$env_file" > "${env_file}.tmp"
        echo "$new_line" >> "${env_file}.tmp"
        mv "${env_file}.tmp" "$env_file"
    else
        echo "$new_line" >> "$env_file"
    fi
}

# ─────────────────────────────────────────
# Installation Steps
# ─────────────────────────────────────────

check_requirements() {
    print_step "Step 1/8 - Checking System Requirements"

    local has_errors=0

    if command_exists php; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        if php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"; then
            print_success "PHP $PHP_VERSION"
        else
            print_error "PHP >= 8.2 required (current: $PHP_VERSION)"
            has_errors=1
        fi
    else
        print_error "PHP is not installed"
        has_errors=1
    fi

    if command_exists composer; then
        print_success "Composer $(composer --version 2>/dev/null | grep -oP '\d+\.\d+\.\d+' | head -1)"
    else
        print_error "Composer is not installed"
        has_errors=1
    fi

    command_exists node && print_success "Node.js $(node --version)" || print_warning "Node.js not installed (optional)"
    command_exists npm && print_success "NPM $(npm --version)" || print_warning "NPM not installed (optional)"
    command_exists git && print_success "Git installed" || print_warning "Git not installed"

    if [ $has_errors -eq 1 ]; then
        print_error "\nPlease install missing dependencies first."
        exit 1
    fi

    print_success "\nAll requirements met!"
}

configure_environment() {
    print_step "Step 2/8 - Environment Configuration"

    if [ -f .env ]; then
        print_warning ".env already exists"
        read -p "Reconfigure? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_info "Keeping existing .env"
            return
        fi
    fi

    cp .env.example .env
    print_success "Created .env file"

    echo -e "\n${CYAN}Application Settings:${NC}"
    APP_NAME=$(get_input "App Name" "Lotto Platform")
    APP_ENV=$(get_input "Environment (local/production)" "production")
    APP_DEBUG=$(get_input "Debug Mode (true/false)" "false")
    APP_URL=$(get_input "Application URL" "https://yourdomain.com")

    echo -e "\n${CYAN}Database Configuration:${NC}"
    DB_CONNECTION=$(get_input "Database Type (mysql/sqlite)" "mysql")

    if [ "$DB_CONNECTION" = "mysql" ]; then
        DB_HOST=$(get_input "Database Host" "localhost")
        DB_PORT=$(get_input "Database Port" "3306")
        DB_DATABASE=$(get_input "Database Name" "lotto")
        DB_USERNAME=$(get_input "Database Username" "root")
        DB_PASSWORD=$(get_password "Database Password")

        update_env_var "DB_CONNECTION" "mysql"
        update_env_var "DB_HOST" "$DB_HOST"
        update_env_var "DB_PORT" "$DB_PORT"
        update_env_var "DB_DATABASE" "$DB_DATABASE"
        update_env_var "DB_USERNAME" "$DB_USERNAME"
        update_env_var "DB_PASSWORD" "$DB_PASSWORD"
    else
        update_env_var "DB_CONNECTION" "sqlite"
    fi

    update_env_var "APP_NAME" "$APP_NAME"
    update_env_var "APP_ENV" "$APP_ENV"
    update_env_var "APP_DEBUG" "$APP_DEBUG"
    update_env_var "APP_URL" "$APP_URL"
    update_env_var "APP_TIMEZONE" "Asia/Bangkok"

    print_success "Environment configured"
}

install_dependencies() {
    print_step "Step 3/8 - Installing Dependencies"

    print_info "Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "PHP dependencies installed"

    if command_exists npm; then
        print_info "Installing Node.js dependencies..."
        npm install --silent 2>/dev/null || npm install
        print_success "Node.js dependencies installed"
    else
        print_warning "NPM not available, skipping"
    fi
}

setup_application() {
    print_step "Step 4/8 - Application Setup"

    print_info "Generating application key..."
    php artisan key:generate --force
    print_success "Application key generated"

    print_info "Creating directories..."
    mkdir -p storage/framework/{cache/data,sessions,views}
    mkdir -p storage/logs
    mkdir -p storage/backups
    mkdir -p bootstrap/cache
    print_success "Directories created"

    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    print_success "Permissions set"

    if grep -q "DB_CONNECTION=sqlite" .env; then
        touch database/database.sqlite
        print_success "SQLite database created"
    fi
}

run_migrations() {
    print_step "Step 5/8 - Database Migration"

    print_info "Running migrations..."
    php artisan migrate --force
    print_success "Database migrated"

    # Seed risk settings
    if [ -f database/seeders/RiskSettingsSeeder.php ]; then
        print_info "Seeding risk settings..."
        php artisan db:seed --class=RiskSettingsSeeder --force 2>/dev/null || true
    fi
}

setup_admin() {
    print_step "Step 6/8 - Admin Account Setup"

    echo -e "${CYAN}Create the first Super Admin account:${NC}\n"

    ADMIN_NAME=$(get_input "Admin name" "Administrator")
    ADMIN_EMAIL=$(get_input "Admin email" "")
    ADMIN_PHONE=$(get_input "Admin phone (optional)" "")

    while true; do
        ADMIN_PASSWORD=$(get_password "Admin password (min 8 chars)")
        if [ ${#ADMIN_PASSWORD} -ge 8 ]; then
            break
        fi
        print_error "Password must be at least 8 characters. Try again."
    done

    php artisan app:setup-admin \
        --name="$ADMIN_NAME" \
        --email="$ADMIN_EMAIL" \
        --phone="$ADMIN_PHONE" \
        --password="$ADMIN_PASSWORD" \
        --force

    print_success "Admin account created!"
}

build_assets() {
    print_step "Step 7/8 - Building Frontend"

    if command_exists npm; then
        print_info "Building assets..."
        npm run build
        print_success "Frontend assets built"
    else
        print_warning "NPM not available, skipping asset build"
    fi
}

final_setup() {
    print_step "Step 8/8 - Final Setup"

    php artisan cache:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    print_success "Cache cleared"

    if grep -q "APP_ENV=production" .env; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        print_success "Application optimized for production"
    fi

    php artisan storage:link 2>/dev/null || true
    print_success "Storage linked"
}

print_completion() {
    echo -e "\n${GREEN}"
    echo "╔═══════════════════════════════════════════════════════╗"
    echo "║                                                       ║"
    echo "║      🎉 INSTALLATION COMPLETED SUCCESSFULLY! 🎉      ║"
    echo "║                                                       ║"
    echo "╚═══════════════════════════════════════════════════════╝"
    echo -e "${NC}"

    APP_URL=$(grep "APP_URL=" .env 2>/dev/null | head -1 | cut -d'=' -f2 || echo "http://localhost")

    echo -e "${CYAN}Access your application:${NC}"
    echo -e "   ${GREEN}$APP_URL${NC}"

    echo -e "\n${CYAN}Admin Login:${NC}"
    echo -e "   Email: ${GREEN}$ADMIN_EMAIL${NC}"
    echo -e "   URL:   ${GREEN}$APP_URL/admin${NC}"

    echo -e "\n${CYAN}Useful Commands:${NC}"
    echo -e "  ${GREEN}./deploy.sh${NC}            - Deploy updates"
    echo -e "  ${GREEN}./clear-cache.sh${NC}       - Clear all caches"
    echo -e "  ${GREEN}./fix-permissions.sh${NC}   - Fix file permissions"
    echo -e "  ${GREEN}php artisan serve${NC}      - Start dev server"
    echo -e "  ${GREEN}php artisan app:setup-admin${NC} - Create another admin"

    echo -e "\n${GREEN}Thank you for choosing Lotto Platform!${NC}\n"
}

# ─────────────────────────────────────────
# Main
# ─────────────────────────────────────────

main() {
    clear
    print_logo

    echo -e "${YELLOW}This wizard will guide you through the installation.${NC}"
    echo -e "${YELLOW}Directory: ${GREEN}$SCRIPT_DIR${NC}"
    echo -e "\n${YELLOW}Press Enter to continue or Ctrl+C to cancel...${NC}"
    read

    check_requirements
    configure_environment
    install_dependencies
    setup_application
    run_migrations
    setup_admin
    build_assets
    final_setup
    print_completion
}

main
