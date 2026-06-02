# Guia de Desenvolvimento Local — Docker

> Ambiente completo do Magento 2 + módulo Pagar.me rodando na sua máquina.  
> **Nenhuma credencial do Magento Marketplace é necessária.**

---

## Índice

- [Pré-requisitos](#pré-requisitos)
- [Subindo pela primeira vez](#subindo-pela-primeira-vez)
- [Acessando o ambiente](#acessando-o-ambiente)
- [Makefile — referência rápida](#makefile--referência-rápida)
- [Referência de comandos](#referência-de-comandos)
- [Após alterar o código](#após-alterar-o-código)
- [Banco de dados](#banco-de-dados)
- [Xdebug](#xdebug)
- [E-mails locais (Mailpit)](#e-mails-locais-mailpit)
- [Arquitetura dos containers](#arquitetura-dos-containers)
- [Troubleshooting](#troubleshooting)
- [Destruir o ambiente](#destruir-o-ambiente)

---

## Pré-requisitos

| Requisito | Versão mínima | Download |
|---|---|---|
| Docker Desktop | 4.x | [docker.com](https://www.docker.com/products/docker-desktop/) |
| Git Bash (Windows) | qualquer | incluído no Git for Windows |

Verifique se o Docker está rodando:

```bash
docker info
```

Se retornar erro, abra o Docker Desktop e aguarde o ícone estabilizar na bandeja do sistema.

---

## Subindo pela primeira vez

Execute os três comandos abaixo. O processo completo leva **10–20 minutos** na primeira vez (o Composer baixa o Magento completo via mirror público).

```bash
# 1. Clone o repositório (se ainda não fez)
git clone <url-do-repositorio>
cd magento2

# 2. Torne os scripts executáveis
chmod +x bin/*

# 3. Execute o setup completo
bin/setup
```

O script `bin/setup` faz tudo automaticamente:

```
[setup] Copiando .env.example → .env
[setup] Gerando certificado SSL autoassinado para localhost...
[setup] Construindo imagem Docker...
         └── PHP 8.2 + Nginx + Supervisor + Magento 2.4.7 + módulo Pagar.me
[setup] Subindo todos os serviços...
         └── app, db, elasticsearch, redis, mailpit, phpmyadmin, redis-commander
[setup] Aguardando o Magento finalizar a instalação...
         └── setup:install (cria banco, configura admin, etc.)
[setup] Configurando Redis para cache e sessões...
[setup] Limpando cache...

  ┌──────────────────────────────────────────────────────────┐
  │              Ambiente pronto!                            │
  ├──────────────────────────────────────────────────────────┤
  │  Loja:           https://localhost:8443               │
  │  Admin:          https://localhost:8443/admin          │
  │  phpMyAdmin:     http://localhost:8081                 │
  │  Mailpit:        http://localhost:8025                 │
  │  Redis Commander: http://localhost:8082                │
  ├──────────────────────────────────────────────────────────┤
  │  Admin user:     admin                               │
  │  Admin pass:     Admin123!                           │
  └──────────────────────────────────────────────────────────┘
```

> **Aviso do browser:** ao acessar `https://localhost:8443` o browser alertará sobre o certificado autoassinado. Clique em **Avançado → Continuar para localhost (não seguro)**. Isso é esperado em ambiente local.

---

## Acessando o ambiente

| Serviço | URL | Credenciais |
|---|---|---|
| **Loja Magento** | https://localhost:8443 | — |
| **Painel Admin** | https://localhost:8443/admin | `admin` / `Admin123!` |
| **phpMyAdmin** | http://localhost:8081 | `magento` / `magento` |
| **Mailpit** (e-mails) | http://localhost:8025 | — |
| **Redis Commander** | http://localhost:8082 | — |
| **Elasticsearch** | http://localhost:9200 | — |
| **MariaDB** | `localhost:3306` | `magento` / `magento` |

---

## Makefile — referência rápida

Todos os comandos do ambiente estão disponíveis via `make`. Execute `make help` para ver a lista completa:

```
  setup                Configura e sobe o ambiente do zero (primeira vez)
  up                   Sobe todos os containers
  down                 Para todos os containers (dados preservados)
  rebuild              Reconstrói a imagem após mudança no código
  restart              Reinicia o container app sem rebuild
  destroy              Remove containers, volumes e dados (irreversível)

  magento              Executa comando Magento. Ex: make magento CMD="cache:flush"
  cache-flush          Limpa todo o cache do Magento
  upgrade              Roda setup:upgrade + cache:flush
  compile              Roda setup:di:compile
  static               Deploy do conteúdo estático (pt_BR)
  reindex              Reindexa todos os indexadores

  composer             Executa comando Composer. Ex: make composer CMD="require pkg"

  shell                Abre shell bash dentro do container app
  db                   Abre o cliente MySQL no container db
  logs                 Logs do container app em tempo real
  logs-all             Logs de todos os containers

  ps                   Lista containers e status
  urls                 Exibe todas as URLs de acesso

  sonar                Roda o Sonar Scanner
  sonar-check-quality-gate  Verifica o quality gate do Sonar
```

### Exemplos de uso

```bash
make setup                              # primeira vez
make up                                 # ligar
make down                               # desligar
make rebuild                            # após mudar código
make magento CMD="cache:flush"          # qualquer comando Magento
make magento CMD="module:status"
make magento CMD="setup:upgrade"
make composer CMD="require vendor/pkg"  # qualquer comando Composer
make shell                              # bash no container
make db                                 # MySQL no container
make logs                               # tail dos logs do app
make urls                               # lembra as URLs e senhas
make destroy                            # apaga tudo
```

---

## Referência de comandos

### Ligar e desligar

```bash
bin/start          # sobe os containers (dados preservados)
bin/stop           # para os containers (dados preservados)
docker compose down -v   # para E APAGA todos os dados
```

### Comandos do Magento

```bash
# Sintaxe: bin/magento <comando>
bin/magento cache:flush
bin/magento cache:status
bin/magento module:status
bin/magento module:enable Pagarme_Pagarme
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f pt_BR
bin/magento indexer:reindex
bin/magento deploy:mode:set developer
bin/magento deploy:mode:show
```

### Comandos do Composer

```bash
# Sintaxe: bin/composer <comando>
bin/composer install
bin/composer update
bin/composer require vendor/pacote
bin/composer show pagarme/pagarme-magento2-module
```

### Shell interativo no container

```bash
bin/shell
# Você estará em /var/www/html dentro do container
# php bin/magento, composer, mysql etc. disponíveis diretamente
```

### Ver logs

```bash
bin/logs                          # logs do app (PHP-FPM + Nginx) — follow
bin/logs db                       # logs do MariaDB
bin/logs elasticsearch            # logs do Elasticsearch
docker compose logs --tail=50 app # últimas 50 linhas sem follow
```

---

## Após alterar o código

A estratégia do ambiente é **rebuild**: qualquer mudança no código do módulo precisa de uma reconstrução da imagem para ser aplicada.

```bash
bin/rebuild
```

O que esse comando faz:

```
[rebuild] Parando containers...
[rebuild] Construindo nova imagem...
           └── Composer copia o módulo local para dentro da imagem
           └── setup:upgrade roda automaticamente na inicialização
[rebuild] Subindo containers...
[rebuild] Pronto. Acompanhe os logs com: bin/logs
```

> **Dica:** rebuilds subsequentes são muito mais rápidos (~2–3 min) porque o Docker reutiliza o cache da camada do Magento. Apenas a camada do módulo é recompilada.

---

## Banco de dados

### Acesso via phpMyAdmin (interface web)

Acesse http://localhost:8081 e use as credenciais:
- **Servidor:** `db`
- **Usuário:** `magento`
- **Senha:** `magento`

### Acesso via terminal

```bash
# Como usuário magento
docker compose exec db mysql -u magento -pmagento magento

# Como root (acesso total)
docker compose exec db mysql -u root -proot
```

### Importar um dump SQL

```bash
docker compose exec -T db mysql -u magento -pmagento magento < meu-dump.sql
```

### Exportar um dump SQL

```bash
docker compose exec db mysqldump -u magento -pmagento magento > backup.sql
```

---

## Xdebug

O Xdebug 3 está instalado na imagem de desenvolvimento, mas **desativado por padrão** para não impactar a performance do dia a dia.

### Ativar

1. Edite `docker/php/xdebug.ini`:

```ini
; Mude "off" para "debug"
xdebug.mode = debug
```

2. Reconstrua o container:

```bash
bin/rebuild
```

### Desativar

Volte o `xdebug.mode = off` e rode `bin/rebuild`.

### Configuração no VS Code

Instale a extensão [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) e crie o arquivo `.vscode/launch.json` na raiz do projeto:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Xdebug — Magento (Docker)",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html/app/code/Pagarme/Pagarme": "${workspaceFolder}"
      }
    }
  ]
}
```

Pressione `F5` para iniciar a escuta. O Xdebug conectará automaticamente quando uma requisição PHP for feita.

### Configuração no PHPStorm

1. **Settings → PHP → Debug**: confirme que a porta está em `9003`
2. **Settings → PHP → Servers**: adicione um servidor:
   - **Name:** `localhost`
   - **Host:** `localhost`
   - **Port:** `8443`
   - **Debugger:** Xdebug
   - **Path mapping:** `/var/www/html/app/code/Pagarme/Pagarme` → `<raiz do projeto>`
3. Clique no ícone de telefone 🐛 para começar a escutar

---

## E-mails locais (Mailpit)

Todos os e-mails enviados pelo Magento são **interceptados pelo Mailpit** — nenhum e-mail chega ao destinatário real.

Acesse http://localhost:8025 para visualizar a caixa de entrada.

Para configurar o Magento para enviar via SMTP do Mailpit, instale um módulo de SMTP (ex: `mageplaza/magento-2-smtp`) e configure no Admin:
- **SMTP Host:** `mailpit`
- **SMTP Port:** `1025`
- **Authentication:** None

---

## Arquitetura dos containers

```
  ┌─────────────────── Host (sua máquina) ────────────────────────┐
  │                                                                │
  │   Browser ──→ https://localhost:8443                          │
  │                      │                                        │
  │         ┌────────────▼────────────┐                           │
  │         │         app             │  PHP 8.2-FPM + Nginx      │
  │         │   /var/www/html         │  + Supervisor             │
  │         │   (Magento 2.4.7        │  porta 80 (http)          │
  │         │    + módulo Pagar.me)   │  porta 443 (https)        │
  │         └────┬──────┬──────┬──────┘                           │
  │              │      │      │                                  │
  │         ┌────▼─┐ ┌──▼──┐ ┌▼─────────────┐                   │
  │         │  db  │ │redis│ │elasticsearch  │                   │
  │         │MariaDB│ │  7  │ │    7.17       │                   │
  │         │ 10.6  │ │     │ │               │                   │
  │         └──────┘ └─────┘ └───────────────┘                   │
  │                                                                │
  │   Ferramentas:                                                 │
  │   ┌───────────┐  ┌─────────────────┐  ┌────────────────────┐  │
  │   │  Mailpit  │  │   phpMyAdmin    │  │  Redis Commander   │  │
  │   │ :8025     │  │    :8081        │  │      :8082         │  │
  │   └───────────┘  └─────────────────┘  └────────────────────┘  │
  └────────────────────────────────────────────────────────────────┘
```

**Volumes persistentes:**
- `db_data` → dados do MariaDB (sobrevive ao `docker compose down`)
- `es_data` → índices do Elasticsearch

**Volumes temporários (perdidos ao destruir o ambiente):**
- Código Magento + módulo (baked na imagem, recriado no `bin/rebuild`)

---

## Troubleshooting

### O `bin/setup` travou aguardando o Magento

Abra outro terminal e veja o que está acontecendo:

```bash
bin/logs
```

Causas comuns:
- **"DB not ready yet"** → O MariaDB ainda está inicializando. Aguarde ou verifique `bin/logs db`
- **"Elasticsearch not ready yet"** → Aguarde. O Elasticsearch demora ~60s para ficar saudável
- **"Composer memory limit"** → Aumente a memória do Docker Desktop (mínimo recomendado: 4 GB)

### Erro `port is already allocated`

Alguma porta está em uso na sua máquina. Identifique e pare o processo:

```bash
# Descobrir qual processo usa a porta (ex: 3306)
netstat -ano | findstr :3306   # Windows
lsof -i :3306                  # Linux/macOS

# Ou mude a porta no docker-compose.yml
# Ex: "3307:3306" para mapear na porta 3307 do host
```

### Erro ao buildar: `failed to solve: ...`

Problema com o cache do Docker. Force um rebuild limpo:

```bash
docker compose build --no-cache
```

### O browser mostra "502 Bad Gateway"

O PHP-FPM ainda não subiu. Aguarde alguns segundos e recarregue. Se persistir:

```bash
bin/logs           # verifique erros do PHP-FPM
bin/magento cache:flush
```

### O Admin redireciona para `https://localhost/` (sem porta)

A URL base do Magento está errada. Corrija via CLI:

```bash
bin/magento config:set web/unsecure/base_url http://localhost:8080/
bin/magento config:set web/secure/base_url https://localhost:8443/
bin/magento config:set web/secure/use_in_frontend 1
bin/magento config:set web/secure/use_in_adminhtml 1
bin/magento cache:flush
```

### Erro `Class X does not exist` ou `Area code not set`

Geração de código desatualizada. Rode dentro do container:

```bash
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

### O Elasticsearch retorna status `red`

```bash
bin/logs elasticsearch
```

Causa mais comum no Linux: limite de memória virtual do kernel. Execute no host:

```bash
sudo sysctl -w vm.max_map_count=262144
```

Para tornar permanente, adicione `vm.max_map_count=262144` em `/etc/sysctl.conf`.

### Mudei o código mas não refletiu

Lembre-se: a estratégia é rebuild. Rode:

```bash
bin/rebuild
```

### Preciso resetar tudo do zero

```bash
# Para, remove containers, volumes e imagens locais
docker compose down -v --rmi local

# Sobe novamente do zero
bin/setup
```

---

## Destruir o ambiente

```bash
# Para os containers e preserva os dados (volumes)
bin/stop

# Para e apaga todos os dados (banco, índices ES)
docker compose down -v

# Para, apaga dados E remove as imagens construídas
docker compose down -v --rmi local
```

> Após `down -v`, o próximo `bin/setup` reinstalará o Magento completamente do zero.
