#!/bin/bash

#########################################################
# Lotto Platform - Fix File Permissions
#########################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo -e "${CYAN}Fixing file permissions...${NC}\n"

# Detect web server user
WEB_USER=""
if id "www-data" &>/dev/null; then
    WEB_USER="www-data"
elif id "apache" &>/dev/null; then
    WEB_USER="apache"
elif id "nginx" &>/dev/null; then
    WEB_USER="nginx"
fi

# Set directory permissions
find . -type d -not -path './vendor/*' -not -path './node_modules/*' -not -path './.git/*' -exec chmod 755 {} \; 2>/dev/null || true
echo -e "${GREEN}✓ Directory permissions set to 755${NC}"

# Set file permissions
find . -type f -not -path './vendor/*' -not -path './node_modules/*' -not -path './.git/*' -exec chmod 644 {} \; 2>/dev/null || true
echo -e "${GREEN}✓ File permissions set to 644${NC}"

# Make shell scripts executable
chmod +x *.sh 2>/dev/null || true
echo -e "${GREEN}✓ Shell scripts made executable${NC}"

# Make artisan executable
chmod +x artisan 2>/dev/null || true
echo -e "${GREEN}✓ Artisan made executable${NC}"

# Writable directories
chmod -R 775 storage 2>/dev/null || true
chmod -R 775 bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓ Storage & cache set to 775${NC}"

# Create required directories
mkdir -p storage/framework/{cache/data,sessions,views}
mkdir -p storage/logs
mkdir -p storage/backups
mkdir -p bootstrap/cache
echo -e "${GREEN}✓ Required directories created${NC}"

# Set web server ownership if possible
if [ -n "$WEB_USER" ]; then
    chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache 2>/dev/null || {
        echo -e "${YELLOW}⚠ Could not set ownership to $WEB_USER (run as root/sudo)${NC}"
    }
    echo -e "${GREEN}✓ Ownership set to $WEB_USER${NC}"
else
    echo -e "${YELLOW}⚠ Web server user not detected (www-data/apache/nginx)${NC}"
    echo -e "${YELLOW}  You may need to manually set ownership${NC}"
fi

echo -e "\n${GREEN}✓ Permissions fixed!${NC}\n"
