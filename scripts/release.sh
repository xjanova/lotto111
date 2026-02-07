#!/bin/bash

# Automated Release Script
# Usage: ./scripts/release.sh [patch|minor|major]

set -e

TYPE=${1:-patch}

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Creating ${TYPE} release...${NC}"

# Ensure we're on main branch
BRANCH=$(git branch --show-current)
if [ "$BRANCH" != "main" ] && [ "$BRANCH" != "develop" ]; then
    echo -e "${RED}Error: Must be on main or develop branch${NC}"
    exit 1
fi

# Ensure working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}Error: Working directory is not clean${NC}"
    exit 1
fi

# Get current version
CURRENT_TAG=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
CURRENT_VERSION=${CURRENT_TAG#v}

# Parse version components
IFS='.' read -r MAJOR MINOR PATCH <<< "$CURRENT_VERSION"

# Calculate new version
case $TYPE in
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    patch)
        PATCH=$((PATCH + 1))
        ;;
    *)
        echo -e "${RED}Error: Invalid type. Use patch, minor, or major${NC}"
        exit 1
        ;;
esac

NEW_VERSION="${MAJOR}.${MINOR}.${PATCH}"
NEW_TAG="v${NEW_VERSION}"

echo -e "${GREEN}Current version: ${CURRENT_TAG}${NC}"
echo -e "${GREEN}New version:     ${NEW_TAG}${NC}"

# Confirm
read -p "Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

# Pull latest
git pull origin "$BRANCH"

# Run tests
echo -e "${YELLOW}Running tests...${NC}"
php artisan test

# Run quality checks
echo -e "${YELLOW}Running quality checks...${NC}"
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M

# Create tag
echo -e "${YELLOW}Creating tag ${NEW_TAG}...${NC}"
git tag -a "$NEW_TAG" -m "Release ${NEW_TAG}"

# Push tag
echo -e "${YELLOW}Pushing tag...${NC}"
git push origin "$NEW_TAG"

echo -e "${GREEN}Release ${NEW_TAG} created and pushed!${NC}"
echo -e "${GREEN}GitHub Actions will automatically create the release.${NC}"
echo -e "${GREEN}Check: https://github.com/xjanova/lotto111/releases${NC}"
