#!/bin/bash

#########################################################
# Lotto Platform - Clear All Caches
#########################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo -e "${CYAN}Clearing all caches...${NC}\n"

php artisan cache:clear 2>/dev/null && echo -e "${GREEN}✓ Application cache cleared${NC}" || true
php artisan config:clear 2>/dev/null && echo -e "${GREEN}✓ Config cache cleared${NC}" || true
php artisan route:clear 2>/dev/null && echo -e "${GREEN}✓ Route cache cleared${NC}" || true
php artisan view:clear 2>/dev/null && echo -e "${GREEN}✓ View cache cleared${NC}" || true
php artisan event:clear 2>/dev/null && echo -e "${GREEN}✓ Event cache cleared${NC}" || true

# Clear compiled files
if [ -f bootstrap/cache/config.php ]; then
    rm -f bootstrap/cache/config.php
    echo -e "${GREEN}✓ Compiled config removed${NC}"
fi

if [ -f bootstrap/cache/routes-v7.php ]; then
    rm -f bootstrap/cache/routes-v7.php
    echo -e "${GREEN}✓ Compiled routes removed${NC}"
fi

# Optionally rebuild for production
if grep -q "APP_ENV=production" .env 2>/dev/null; then
    echo -e "\n${CYAN}Rebuilding production caches...${NC}\n"
    php artisan config:cache && echo -e "${GREEN}✓ Config cached${NC}"
    php artisan route:cache && echo -e "${GREEN}✓ Routes cached${NC}"
    php artisan view:cache && echo -e "${GREEN}✓ Views cached${NC}"
fi

echo -e "\n${GREEN}✓ All caches cleared!${NC}\n"
