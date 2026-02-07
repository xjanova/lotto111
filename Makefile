.PHONY: help install dev test quality lint analyse rector build deploy clean docker-up docker-down fresh seed

# Colors
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)

## Help
help: ## Show this help
	@echo ''
	@echo 'Usage:'
	@echo '  ${YELLOW}make${RESET} ${GREEN}<target>${RESET}'
	@echo ''
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  ${YELLOW}%-15s${RESET} %s\n", $$1, $$2}' $(MAKEFILE_LIST)

## Installation
install: ## Install all dependencies
	composer install
	npm install
	cp -n .env.example .env || true
	php artisan key:generate --no-interaction
	php artisan migrate --seed
	npm run build

## Development
dev: ## Start development servers
	php artisan serve & npm run dev & php artisan queue:work --sleep=3 & wait

serve: ## Start PHP development server
	php artisan serve

watch: ## Start Vite dev server
	npm run dev

queue: ## Start queue worker
	php artisan queue:work redis --queue=high,default,low --sleep=3 --tries=3

reverb: ## Start WebSocket server
	php artisan reverb:start

schedule: ## Start scheduler
	php artisan schedule:work

## Testing
test: ## Run all tests
	php artisan test

test-coverage: ## Run tests with coverage report
	php artisan test --coverage --min=70

test-unit: ## Run unit tests only
	php artisan test --testsuite=Unit

test-feature: ## Run feature tests only
	php artisan test --testsuite=Feature

test-parallel: ## Run tests in parallel
	php artisan test --parallel

## Code Quality
quality: lint analyse ## Run all quality checks

lint: ## Run Laravel Pint (code formatter)
	./vendor/bin/pint

lint-check: ## Check code style without fixing
	./vendor/bin/pint --test

analyse: ## Run PHPStan static analysis
	./vendor/bin/phpstan analyse --memory-limit=512M

rector: ## Run Rector (dry-run)
	./vendor/bin/rector process --dry-run

rector-fix: ## Run Rector and apply fixes
	./vendor/bin/rector process

## Build
build: ## Build production assets
	npm run build

## Database
fresh: ## Fresh migrate with seed
	php artisan migrate:fresh --seed

seed: ## Run database seeders
	php artisan db:seed

migrate: ## Run migrations
	php artisan migrate

## Cache
cache: ## Cache all configuration
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache

cache-clear: ## Clear all caches
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	php artisan event:clear
	php artisan cache:clear

## Docker
docker-up: ## Start Docker containers
	docker compose up -d

docker-down: ## Stop Docker containers
	docker compose down

docker-build: ## Build Docker image
	docker compose build --no-cache

docker-logs: ## View Docker logs
	docker compose logs -f

docker-shell: ## Shell into app container
	docker compose exec app sh

## Release
release-patch: ## Create a patch release (x.x.X)
	@./scripts/release.sh patch

release-minor: ## Create a minor release (x.X.0)
	@./scripts/release.sh minor

release-major: ## Create a major release (X.0.0)
	@./scripts/release.sh major

## Cleanup
clean: ## Clean generated files
	rm -rf vendor node_modules
	rm -rf .phpunit.cache
	rm -rf storage/logs/*.log
	php artisan config:clear
	php artisan cache:clear
