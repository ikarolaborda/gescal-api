.PHONY: help build up down restart logs shell mysql redis test pint migrate seed fresh

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build Docker containers
	docker-compose build

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## Show logs from all containers
	docker-compose logs -f

logs-app: ## Show logs from app container
	docker-compose logs -f app

shell: ## Open shell in app container
	docker-compose exec app sh

mysql: ## Open MySQL shell
	docker-compose exec mysql mysql -u gescal_user -pgescal_pass gescal

redis: ## Open Redis CLI
	docker-compose exec redis redis-cli

test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

test-filter: ## Run specific test (use FILTER=testName)
	docker-compose exec app php artisan test --filter=$(FILTER)

pint: ## Run Pint code formatter
	docker-compose exec app vendor/bin/pint

pint-dirty: ## Run Pint on dirty files only
	docker-compose exec app vendor/bin/pint --dirty

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migrations (WARNING: drops all tables)
	docker-compose exec app php artisan migrate:fresh

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migrations with seeding
	docker-compose exec app php artisan migrate:fresh --seed

composer-install: ## Install Composer dependencies
	docker-compose exec app composer install

composer-update: ## Update Composer dependencies
	docker-compose exec app composer update

npm-install: ## Install NPM dependencies
	docker-compose exec app npm install

npm-dev: ## Run NPM dev server
	docker-compose exec app npm run dev

npm-build: ## Build frontend assets
	docker-compose exec app npm run build

horizon: ## Open Horizon dashboard (http://localhost:8000/horizon)
	@echo "Horizon dashboard: http://localhost:8000/horizon"

telescope: ## Open Telescope dashboard (http://localhost:8000/telescope)
	@echo "Telescope dashboard: http://localhost:8000/telescope"

mailhog: ## Open Mailhog dashboard (http://localhost:8025)
	@echo "Mailhog dashboard: http://localhost:8025"

cache-clear: ## Clear application cache
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimize application for production
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

init: build up composer-install migrate-fresh seed ## Initial setup (build, start, install, migrate, seed)
	@echo "Application ready at http://localhost:8000"

