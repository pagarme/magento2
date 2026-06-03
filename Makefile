.DEFAULT_GOAL := help
SHELL         := /usr/bin/env bash

COMPOSE     = docker compose
APP_SERVICE = app
MODULE      ?= Pagarme_Pagarme

# ── Dev Environment ───────────────────────────────────────────────────────────

up: ## Start all services (builds images if needed)
	$(COMPOSE) up -d --build
.PHONY: up

down: ## Stop and remove containers, networks
	$(COMPOSE) down
.PHONY: down

build: ## Rebuild images from scratch
	$(COMPOSE) build --no-cache
.PHONY: build

restart: ## Restart all services
	$(COMPOSE) restart
.PHONY: restart

logs: ## Follow logs — pass s=<service> to filter (e.g. make logs s=nginx)
	$(COMPOSE) logs -f $(s)
.PHONY: logs

shell: ## Open a bash shell inside the app (PHP-FPM) container
	$(COMPOSE) exec $(APP_SERVICE) bash
.PHONY: shell

ps: ## Show status of running containers
	$(COMPOSE) ps
.PHONY: ps

# ── Magento ───────────────────────────────────────────────────────────────────

install-magento: ## Download Magento via Composer and run setup:install (first-time only)
	@bash scripts/install-magento.sh
.PHONY: install-magento

clean-magento: ## Wipe src/ via container root (safe after a failed install)
	@bash scripts/clean-magento.sh
.PHONY: clean-magento

module-enable: ## Enable MODULE (default: Pagarme_Pagarme) and run upgrade/compile/cache
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento module:enable $(MODULE)
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento setup:upgrade
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento setup:di:compile
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento cache:clean
.PHONY: module-enable

disable-2fa: ## Disable Magento 2FA (dev only — never use in production)
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth
	$(COMPOSE) exec $(APP_SERVICE) php bin/magento cache:flush
.PHONY: disable-2fa

# ── Sonar ─────────────────────────────────────────────────────────────────────

sonar: ## Run SonarQube scanner
	docker run -ti -v $(shell pwd):/usr/src/ pagarme/sonar-scanner -Dsonar.branch.name=${BRANCH}
.PHONY: sonar

sonar-check-quality-gate: ## Check Sonar quality gate result
	docker run -v $(shell pwd):/usr/src/sonar pagarme/check-sonar-quality-gate
.PHONY: sonar-check-quality-gate

# ── Help ──────────────────────────────────────────────────────────────────────

help: ## List available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2}'
.PHONY: help
