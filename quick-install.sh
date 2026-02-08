#!/bin/bash

#########################################################
# Lotto Platform - Quick Install (Non-Interactive)
# Usage: ./quick-install.sh [admin-email] [admin-password]
#########################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

ADMIN_EMAIL="${1:-admin@lotto.local}"
ADMIN_PASSWORD="${2:-password123}"

echo -e "${CYAN}"
echo "╔═══════════════════════════════════════════════════════╗"
echo "║       ⚡ LOTTO PLATFORM - QUICK INSTALL  ⚡          ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo -e "${NC}"

# Step 1: Environment
echo -e "${CYAN}[1/6] Setting up environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ .env created${NC}"
else
    echo -e "${YELLOW}⚠ .env already exists, keeping it${NC}"
fi

# Step 2: Dependencies
echo -e "${CYAN}[2/6] Installing dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✓ PHP dependencies installed${NC}"

if command -v npm &>/dev/null; then
    npm install --silent 2>/dev/null || npm install
    echo -e "${GREEN}✓ Node.js dependencies installed${NC}"
fi

# Step 3: App key
echo -e "${CYAN}[3/6] Generating app key...${NC}"
php artisan key:generate --force
echo -e "${GREEN}✓ App key generated${NC}"

# Step 4: Directories & permissions
echo -e "${CYAN}[4/6] Setting up directories...${NC}"
mkdir -p storage/framework/{cache/data,sessions,views}
mkdir -p storage/logs storage/backups bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if grep -q "DB_CONNECTION=sqlite" .env 2>/dev/null; then
    touch database/database.sqlite
    echo -e "${GREEN}✓ SQLite database created${NC}"
fi
echo -e "${GREEN}✓ Directories ready${NC}"

# Step 5: Database
echo -e "${CYAN}[5/6] Running migrations...${NC}"
php artisan migrate --force
echo -e "${GREEN}✓ Database migrated${NC}"

# Seed risk settings if available
php artisan db:seed --class=RiskSettingsSeeder --force 2>/dev/null || true

# Step 6: Admin
echo -e "${CYAN}[6/6] Creating admin account...${NC}"
php artisan app:setup-admin \
    --name="Administrator" \
    --email="$ADMIN_EMAIL" \
    --password="$ADMIN_PASSWORD" \
    --force
echo -e "${GREEN}✓ Admin created${NC}"

# Build assets
if command -v npm &>/dev/null; then
    npm run build 2>/dev/null || true
fi

# Optimize
php artisan storage:link 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo -e "\n${GREEN}"
echo "╔═══════════════════════════════════════════════════════╗"
echo "║       ✅ QUICK INSTALL COMPLETE! ✅                  ║"
echo "╚═══════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo -e "Admin Email:    ${GREEN}$ADMIN_EMAIL${NC}"
echo -e "Admin Password: ${GREEN}$ADMIN_PASSWORD${NC}"
echo -e "\nStart dev server: ${CYAN}php artisan serve${NC}\n"
