.DEFAULT_GOAL := help

# ── Variáveis ─────────────────────────────────────────────────────────────────
CMD        ?=
BRANCH     ?=
COMPOSE    := docker compose

# ── Help ──────────────────────────────────────────────────────────────────────
.PHONY: help
help: ## Exibe esta ajuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ── Ambiente ──────────────────────────────────────────────────────────────────
.PHONY: setup
setup: ## Configura e sobe o ambiente do zero (primeira vez)
	@chmod +x bin/*
	@bin/setup
	@$(MAKE) --no-print-directory fix-vendor-bugs
	@echo "Compilando injeção de dependência..."
	@$(COMPOSE) exec -T app php bin/magento setup:di:compile
	@echo "Gerando arquivos estáticos..."
	@$(COMPOSE) exec -T app php bin/magento setup:static-content:deploy -f
	@$(COMPOSE) exec -T app php bin/magento cache:flush

.PHONY: up
up: ## Sobe todos os containers e faz deploy do conteúdo estático
	@$(COMPOSE) up -d
	@echo "Aguardando o container app estabilizar (setup:install pode demorar alguns minutos)..."
	@_attempts=0; \
	until $(COMPOSE) exec -T app supervisorctl status 2>/dev/null | grep -q RUNNING; do \
		_attempts=$$((_attempts + 1)); \
		if [ "$$_attempts" -ge 12 ]; then \
			echo ""; \
			echo "Aviso: timeout aguardando o Supervisor. Continuando mesmo assim..."; \
			break; \
		fi; \
		printf '.'; sleep 10; \
	done
	@echo ""
	@$(MAKE) --no-print-directory fix-vendor-bugs
	@echo "Compilando injeção de dependência (Garantindo generated/code)..."
	@$(COMPOSE) exec -T app php bin/magento setup:di:compile
	@echo "Gerando arquivos estáticos..."
	@$(COMPOSE) exec -T app php bin/magento setup:static-content:deploy -f
	@$(COMPOSE) exec -T app php bin/magento cache:flush

.PHONY: down
down: ## Para todos os containers (dados preservados)
	@$(COMPOSE) down

.PHONY: rebuild
rebuild: ## Reconstrói a imagem e reinicia após mudança no código
	@chmod +x bin/*
	@bin/rebuild

.PHONY: restart
restart: ## Reinicia o container app sem rebuild
	@$(COMPOSE) restart app

.PHONY: destroy
destroy: ## Remove containers, volumes e dados (irreversível)
	@echo "Isso vai apagar todos os dados (banco, índices). Confirma? [s/N] " && read ans && [ "$${ans}" = "s" ]
	@$(COMPOSE) down -v --rmi local

# ── Magento CLI ───────────────────────────────────────────────────────────────
.PHONY: fix-vendor-bugs
fix-vendor-bugs: ## Executa todos os passos de correção, limpa caches, gera CSS e desativa o 2FA
	@echo "=> [1] Substituindo a linha do 'am' por uma checagem segura..."
	@$(COMPOSE) exec -T app sed -i "114s/.*/\$$this->assign('am', \$$this->encoder->encode(\$$localeData['calendar']['gregorian']['AmPmMarkers']['0'] ?? ''));/" /var/www/html/vendor/magento/framework/View/Element/Html/Calendar.php

	@echo "=> [2] Substituindo a linha do 'pm' por uma checagem segura..."
	@$(COMPOSE) exec -T app sed -i "115s/.*/\$$this->assign('pm', \$$this->encoder->encode(\$$localeData['calendar']['gregorian']['AmPmMarkers']['1'] ?? ''));/" /var/www/html/vendor/magento/framework/View/Element/Html/Calendar.php

	@echo "=> [3] Apagando os interceptors gerados..."
	@$(COMPOSE) exec -T app rm -rf /var/www/html/generated/code/*

	@echo "=> [4] Limpando o cache interno do Magento..."
	@$(COMPOSE) exec -T app php bin/magento cache:flush

	@echo "=> Verificando alteração no arquivo (Linhas 114 e 115):"
	@$(COMPOSE) exec -T app sed -n '114,115p' /var/www/html/vendor/magento/framework/View/Element/Html/Calendar.php

	@echo "=> [5] Removendo TODOS os arquivos gerados antigos e lixos de CSS Less..."
	@$(COMPOSE) exec -T app rm -rf /var/www/html/generated/code/*
	@$(COMPOSE) exec -T app rm -rf /var/www/html/var/view_preprocessed/*
	@$(COMPOSE) exec -T app rm -rf /var/www/html/var/page_cache/*
	@$(COMPOSE) exec -T app rm -rf /var/www/html/pub/static/frontend/*
	@$(COMPOSE) exec -T app rm -rf /var/www/html/pub/static/adminhtml/*

	@echo "=> [6] Reiniciando o container app para limpar o OPcache..."
	@$(COMPOSE) restart app

	@echo "=> [7] Limpando os caches internos do Magento de novo..."
	@$(COMPOSE) exec -T app php bin/magento cache:flush

	@echo "=> [8] Garantindo que as permissões das pastas estejam corretas..."
	@$(COMPOSE) exec -u root -T app chmod -R 775 var pub/static generated

	@echo "=> [9] Executando o deploy rápido (quick) ignorando quebras de LESS do frontend..."
	@$(COMPOSE) exec -T app php bin/magento setup:static-content:deploy -f pt_BR en_US --strategy=quick --area=adminhtml || true
	@$(COMPOSE) exec -T app php bin/magento setup:static-content:deploy -f pt_BR en_US --strategy=quick --area=frontend || true

	@echo "=> [10] Desativando o 2FA e suas dependências da Adobe para ambiente local..."
	@$(COMPOSE) exec -T app php bin/magento module:disable Magento_AdminAdobeImsTwoFactorAuth Magento_TwoFactorAuth --clear-static-content || true

	@echo "=> [11] Limpando e atualizando todo o cache do Magento para consolidar..."
	@$(COMPOSE) exec -T app php bin/magento cache:flush
	@echo "================================================================="
	@echo "   PRONTO! TELA CORRIGIDA, CSS GERADO E 2FA DESATIVADO DO MAPA   "
	@echo "================================================================="

.PHONY: magento
magento: ## Executa comando Magento. Ex: make magento CMD="cache:flush"
	@$(COMPOSE) exec app php bin/magento $(CMD)

.PHONY: cache-flush
cache-flush: ## Limpa todo o cache do Magento
	@$(COMPOSE) exec app php bin/magento cache:flush

.PHONY: upgrade
upgrade: ## Roda setup:upgrade + cache:flush
	@$(COMPOSE) exec app php bin/magento setup:upgrade
	@$(COMPOSE) exec app php bin/magento cache:flush

.PHONY: compile
compile: ## Roda setup:di:compile
	@$(COMPOSE) exec app php bin/magento setup:di:compile

.PHONY: static
static: ## Faz deploy do conteúdo estático (pt_BR)
	@$(COMPOSE) exec app php bin/magento setup:static-content:deploy -f pt_BR

.PHONY: reindex
reindex: ## Reindexa todos os indexadores
	@$(COMPOSE) exec app php bin/magento indexer:reindex

# ── Composer ──────────────────────────────────────────────────────────────────
.PHONY: composer
composer: ## Executa comando Composer. Ex: make composer CMD="require vendor/pkg"
	@$(COMPOSE) exec app composer $(CMD)

# ── Acesso ────────────────────────────────────────────────────────────────────
.PHONY: shell
shell: ## Abre shell bash dentro do container app
	@$(COMPOSE) exec app bash

.PHONY: db
db: ## Abre o cliente MySQL no container db
	@$(COMPOSE) exec db mysql -u magento -pmagento magento

.PHONY: logs
logs: ## Exibe os logs do container app em tempo real
	@$(COMPOSE) logs -f app

.PHONY: logs-all
logs-all: ## Exibe os logs de todos os containers
	@$(COMPOSE) logs -f

# ── Status ────────────────────────────────────────────────────────────────────
.PHONY: ps
ps: ## Lista os containers e seus status
	@$(COMPOSE) ps

.PHONY: urls
urls:
	@echo ""
	@echo "  Loja:            https://localhost:8443"
	@echo "  Admin:           https://localhost:8443/admin  (admin / Admin123!)"
	@echo "  phpMyAdmin:      http://localhost:8081         (magento / magento)"
	@echo "  Mailpit:         http://localhost:8025"
	@echo "  Redis Commander: http://localhost:8082"
	@echo "  Elasticsearch:   http://localhost:9200"
	@echo "  Swagger:   	  https://localhost:8443/swagger"
	@echo ""

# ── Qualidade de código ───────────────────────────────────────────────────────
.PHONY: sonar
sonar: ## Roda o Sonar Scanner
	docker run -ti -v $(shell pwd):/usr/src/ pagarme/sonar-scanner -Dsonar.branch.name=$(BRANCH)

.PHONY: sonar-check-quality-gate
sonar-check-quality-gate:
	docker run -v $(shell pwd):/usr/src/sonar pagarme/check-sonar-quality-gate

.PHONY: seed
seed:
	@echo "=> [1] Executando docker/seed.sql no banco..."
	@$(COMPOSE) exec -T db mysql -u magento -pmagento magento < docker/seed.sql
	@echo "=> [2] Registrando no log do módulo..."
	@$(COMPOSE) exec -T app mkdir -p var/log
	@$(COMPOSE) exec -T app sh -c 'echo "[$(shell date "+%Y-%m-%d %H:%M:%S")] INFO pagarme: Seed mock injetado via make seed." >> var/log/pagarme.log'
	@echo "=> [3] Reindexando grids..."
	@$(COMPOSE) exec -T app php bin/magento indexer:reindex customer_grid catalog_product_attribute cataloginventory_stock || true
	@echo "=> [4] Limpando cache..."
	@$(COMPOSE) exec -T app php bin/magento cache:flush
	@echo "Seed concluído. Cliente dev@example.com / Admin123! pronto para uso."

.PHONY: carregar-dados
carregar-dados: seed
