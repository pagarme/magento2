# Magento2 / Pagar.me Integration Module

> Módulo oficial de integração do Pagar.me com Magento 2.

**Quer rodar localmente?** Veja o guia completo em [LOCAL_DEV.md](LOCAL_DEV.md).

## Documentação e Suporte

- [Documentação do módulo](https://github.com/pagarme/magento2-module/wiki)
- [Documentação Pagar.me](https://docs.pagar.me/docs/magento-2-overview)
- [Magento Marketplace](https://docs.pagar.me/docs/split-de-pagamentos-magento-2)
- Suporte comercial: [relacionamento@pagar.me](mailto:relacionamento@pagar.me)

## Instalação via Composer

```bash
composer require pagarme/pagarme-magento2-module
```

### Requisitos

- PHP >= 7.1
- Magento 2 >= 2.3 e <= 2.4.8

### Configuração

Após a instalação acesse **Stores > Settings > Configuration > Sales > Payment Methods > Other Payment Methods > Pagar.me**.

---

## Desenvolvimento Local com Docker

Este repositório inclui um ambiente Docker completo para desenvolvimento e testes do módulo.

### Stack

| Serviço | Versão | Porta local |
|---|---|---|
| PHP-FPM | 8.2 (Alpine) | — |
| Nginx | Alpine | 8080 (HTTP), 8443 (HTTPS) |
| MariaDB | 10.6 | 3306 |
| Elasticsearch | 7.17 | 9200 |
| Redis | 7 | 6379 |
| Mailpit (e-mail trap) | latest | 8025 (UI), 1025 (SMTP) |
| phpMyAdmin | 5 | 8081 |
| Redis Commander | latest | 8082 |

### Pré-requisitos

1. [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado e rodando

> Nenhuma credencial do Magento Marketplace é necessária. O ambiente local usa o **mirror público do Mage-OS** (`mirror.mage-os.org`), que distribui os pacotes Magento CE sem autenticação.

### 1. Configuração inicial (apenas uma vez)

#### 1.1 Executar o setup

```bash
# Torne os scripts executáveis (apenas na primeira vez)
chmod +x bin/*

bin/setup
```

O script irá:
- Copiar `.env.example` → `.env`
- Gerar certificado SSL autoassinado para `localhost`
- Fazer o build da imagem Docker (baixa e instala o Magento + módulo)
- Subir todos os containers
- Aguardar a instalação do Magento finalizar automaticamente
- Configurar Redis para cache de objetos, Full Page Cache e sessões
- Exibir um resumo com todas as URLs de acesso

> Na **primeira execução**, o build pode levar **10–20 minutos** porque o Composer baixa o Magento completo.

### 2. Acessar o ambiente

| URL | Serviço |
|---|---|
| `https://localhost:8443` | Loja Magento |
| `https://localhost:8443/admin` | Painel Admin |
| `http://localhost:8081` | phpMyAdmin |
| `http://localhost:8025` | Mailpit (e-mails) |
| `http://localhost:8082` | Redis Commander |

**Credenciais do Admin:** `admin` / `Admin123!`

> O browser vai exibir um aviso de certificado autoassinado. Clique em **Avançado → Continuar para localhost**.

### 3. Operações do dia a dia

#### Iniciar e parar

```bash
bin/start   # sobe os containers
bin/stop    # para os containers (dados preservados)
```

#### Executar comandos do Magento

```bash
bin/magento cache:flush
bin/magento module:status
bin/magento setup:upgrade
bin/magento indexer:reindex
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f pt_BR
```

#### Executar comandos do Composer

```bash
bin/composer install
bin/composer require vendor/pacote
bin/composer show pagarme/pagarme-magento2-module
```

#### Abrir shell no container

```bash
bin/shell
```

Uma vez dentro do container, você tem acesso direto ao PHP, Composer e ao CLI do Magento em `/var/www/html`.

#### Ver logs

```bash
bin/logs          # logs do app (PHP + Nginx) em tempo real
bin/logs db       # logs do MariaDB
bin/logs -f app   # follow explícito do app
docker compose logs -f elasticsearch
```

### 4. Após alterar o código do módulo

Como a estratégia de rebuild está configurada, após qualquer alteração no código:

```bash
bin/rebuild
```

O script para os containers, reconstrói a imagem (com o novo código compilado) e sobe novamente.

> O Composer instalará o módulo a partir do código local via repositório do tipo `path`. O entrypoint executará `setup:upgrade` automaticamente na inicialização.

### 5. Banco de dados

#### Acesso via phpMyAdmin

Acesse `http://localhost:8081` e faça login com:
- **Servidor:** db
- **Usuário:** magento
- **Senha:** magento

#### Acesso via linha de comando

```bash
docker compose exec db mysql -u magento -pmagento magento
```

Ou como root:

```bash
docker compose exec db mysql -u root -proot
```

#### Importar dump SQL

```bash
docker compose exec -T db mysql -u magento -pmagento magento < dump.sql
```

### 6. Xdebug (debug passo a passo)

O Xdebug 3 está instalado na imagem de dev, mas desativado por padrão para não impactar a performance.

#### Ativar o Xdebug

Edite `docker/php/xdebug.ini` e mude o modo:

```ini
xdebug.mode = debug
```

Reconstrua o container:

```bash
bin/rebuild
```

#### Configuração no VS Code

Instale a extensão [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) e crie `.vscode/launch.json`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
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

#### Configuração no PHPStorm

Vá em **Settings > PHP > Debug > DBGp Proxy** e configure:
- **Host:** localhost
- **Port:** 9003

Em **Settings > PHP > Servers**, adicione um servidor com o path mapping:
- `/var/www/html/app/code/Pagarme/Pagarme` → `${PROJECT_DIR}`

### 7. E-mails transacionais (Mailpit)

Todos os e-mails enviados pelo Magento em ambiente local são capturados pelo Mailpit e **nunca chegam ao destino real**.

Acesse `http://localhost:8025` para visualizar os e-mails.

Para o Magento enviar e-mails via SMTP do Mailpit, instale um módulo SMTP (ex: [mageplaza/magento-2-smtp](https://github.com/mageplaza/magento-2-smtp)) e configure:
- **SMTP Host:** `mailpit`
- **SMTP Port:** `1025`
- **Authentication:** None

### 8. Destruir o ambiente

Para remover containers **e todos os dados** (banco de dados, etc.):

```bash
docker compose down -v
```

Para remover também as imagens construídas:

```bash
docker compose down -v --rmi local
```

---

## Contribuindo

Consulte o [CONTRIBUTING.md](CONTRIBUTING.md).

## Changelog

Veja as [releases](https://github.com/pagarme/magento2-module/releases).

## Licença

MIT — veja [LICENSE](LICENSE).
