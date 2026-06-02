#!/usr/bin/env bash
# =============================================================================
# simular-webhook.sh
# Dispara um POST simulando um postback da Pagar.me contra a instância local.
#
# Rota alvo: POST /rest/V1/pagarme/webhook  (webapi.xml, linha 22)
# Handler  : Pagarme\Pagarme\Model\WebhookManagement::save()
#
# NOTA SOBRE ASSINATURA
# ---------------------
# A Pagar.me assina os webhooks com sua chave RSA privada e o módulo valida com
# a chave pública deles (WebhookValidatorService).  Não é possível gerar uma
# assinatura válida localmente sem a chave privada da Pagar.me.
#
# Opção 1 – Ambiente real (recomendado para staging):
#   Use ngrok/tunnel para expor localhost e deixe a Pagar.me chamar via dashboard.
#
# Opção 2 – Bypass local (dev rápido, padrão deste script):
#   Comente temporariamente o bloco de validação em:
#   Model/WebhookManagement.php  linhas 67-91
#   e passe a flag --skip-sig ao chamar este script.
#
# Opção 3 – Par de chaves próprio:
#   Gere um par RSA local (o script faz isso automaticamente), substitua a
#   verificação do módulo pela sua chave pública de teste e assine os payloads
#   com a chave privada gerada em docker/certs/webhook-test-private.pem.
# =============================================================================
set -euo pipefail

# ─── caminhos ─────────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CERTS_DIR="${SCRIPT_DIR}/certs"
PRIVATE_KEY="${CERTS_DIR}/webhook-test-private.pem"
PUBLIC_KEY="${CERTS_DIR}/webhook-test-public.pem"

# ─── padrões configuráveis ────────────────────────────────────────────────────
BASE_URL="${MAGENTO_HOST:-https://localhost:8443}"
ENDPOINT="${BASE_URL}/rest/V1/pagarme/webhook"
DEFAULT_AMOUNT="189.90"
DEFAULT_TYPE="order.paid"

# ─── cores ────────────────────────────────────────────────────────────────────
if [ -t 1 ]; then
  RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
  CYAN='\033[0;36m'; BOLD='\033[1m'; DIM='\033[2m'; RESET='\033[0m'
else
  RED=''; GREEN=''; YELLOW=''; CYAN=''; BOLD=''; DIM=''; RESET=''
fi

# ─── helpers ──────────────────────────────────────────────────────────────────
info()    { echo -e "${CYAN}[INFO]${RESET}  $*"; }
ok()      { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()    { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
err()     { echo -e "${RED}[ERRO]${RESET}  $*" >&2; }
section() { echo -e "\n${BOLD}── $* ──────────────────────────────────────────${RESET}"; }

# ─── uso ──────────────────────────────────────────────────────────────────────
usage() {
  echo -e "${BOLD}Uso:${RESET}"
  echo "  $0 <ORDER_INCREMENT_ID> [opções]"
  echo
  echo -e "${BOLD}Argumento obrigatório:${RESET}"
  echo "  ORDER_INCREMENT_ID   Increment ID do pedido  (ex: DEV9999900001)"
  echo
  echo -e "${BOLD}Opções:${RESET}"
  printf "  %-28s %s\n" "-t, --type TIPO"       "Tipo do evento (padrão: order.paid)"
  printf "  %-28s %s\n" ""                       "  order.paid | order.payment_failed | charge.refunded"
  printf "  %-28s %s\n" "-a, --amount VALOR"     "Valor em BRL (padrão: ${DEFAULT_AMOUNT})"
  printf "  %-28s %s\n" "-u, --url URL"          "URL base do Magento (padrão: ${BASE_URL})"
  printf "  %-28s %s\n" "-s, --skip-sig"         "Não gera assinatura RSA (requer bypass no PHP)"
  printf "  %-28s %s\n" "-v, --verbose"          "Exibe headers e corpo completo da resposta"
  printf "  %-28s %s\n" "-h, --help"             "Exibe esta ajuda"
  echo
  echo -e "${BOLD}Exemplos:${RESET}"
  echo "  $0 DEV9999900001"
  echo "  $0 DEV9999900002 --type order.payment_failed --amount 49.90"
  echo "  $0 DEV9999900001 --type charge.refunded --verbose"
  echo "  $0 DEV9999900001 --skip-sig"
  echo
  echo -e "${DIM}Variável de ambiente:${RESET}"
  echo "  MAGENTO_HOST   sobrescreve a URL base (padrão: https://localhost:8443)"
  exit 0
}

# ─── garantir par de chaves RSA para testes ───────────────────────────────────
ensure_test_keys() {
  if [ -f "$PRIVATE_KEY" ] && [ -f "$PUBLIC_KEY" ]; then
    return
  fi

  info "Par de chaves RSA de teste não encontrado. Gerando em ${CERTS_DIR}/ ..."
  mkdir -p "$CERTS_DIR"

  openssl genrsa -out "$PRIVATE_KEY" 2048 2>/dev/null
  openssl rsa -in "$PRIVATE_KEY" -pubout -out "$PUBLIC_KEY" 2>/dev/null
  chmod 600 "$PRIVATE_KEY"

  ok "Chaves geradas:"
  echo "   Privada : ${PRIVATE_KEY}"
  echo "   Pública : ${PUBLIC_KEY}"
  echo
  warn "Para que a assinatura seja aceita pelo módulo Magento, configure"
  warn "a chave pública acima no lugar da chave da Pagar.me em:"
  warn "  Model/WebhookManagement.php → WebhookValidatorService::validateSignature"
  warn "Ou use --skip-sig + bypass temporário no PHP para testes rápidos."
  echo
}

# ─── gerar assinatura RSA-SHA256 ──────────────────────────────────────────────
sign_payload() {
  local body="$1"
  printf '%s' "$body" \
    | openssl dgst -sha256 -sign "$PRIVATE_KEY" -binary \
    | base64 | tr -d '\n'
}

# ─── montar payload por tipo de evento ───────────────────────────────────────
build_payload() {
  local order_id="$1"
  local event_type="$2"
  local amount_brl="$3"

  # Pagar.me usa centavos na API; convertemos apenas para o campo 'amount'.
  local amount_cents
  amount_cents=$(printf '%.0f' "$(echo "$amount_brl * 100" | bc)")

  local pagarme_order_id="or_devtest$(printf '%010d' "$RANDOM")"
  local charge_id="ch_devtest$(printf '%010d' "$RANDOM")"
  local hook_id="hook_dev$(printf '%010d' "$RANDOM")"
  local ts
  ts=$(date -u +"%Y-%m-%dT%H:%M:%SZ")

  case "$event_type" in

    "order.paid")
      cat <<JSON
{
  "id": "${hook_id}",
  "type": "order.paid",
  "created_at": "${ts}",
  "data": {
    "order": {
      "id": "${pagarme_order_id}",
      "code": "${order_id}",
      "status": "paid",
      "amount": ${amount_cents},
      "currency": "BRL",
      "charges": [
        {
          "id": "${charge_id}",
          "code": "${order_id}",
          "status": "paid",
          "payment_method": "credit_card",
          "amount": ${amount_cents},
          "paid_amount": ${amount_cents},
          "refunded_amount": 0,
          "last_transaction": {
            "id": "tran_dev0000000001",
            "status": "captured",
            "installments": 2,
            "card": {
              "brand": "visa",
              "last_four_digits": "1111",
              "exp_month": 12,
              "exp_year": 2030
            }
          }
        }
      ],
      "metadata": {
        "platformVersion": "Magento 2 (dev-local)"
      }
    }
  },
  "account": {
    "id": "acc_devtest00000001",
    "name": "Conta Dev Local"
  }
}
JSON
      ;;

    "order.payment_failed")
      cat <<JSON
{
  "id": "${hook_id}",
  "type": "order.payment_failed",
  "created_at": "${ts}",
  "data": {
    "order": {
      "id": "${pagarme_order_id}",
      "code": "${order_id}",
      "status": "failed",
      "amount": ${amount_cents},
      "currency": "BRL",
      "charges": [
        {
          "id": "${charge_id}",
          "code": "${order_id}",
          "status": "failed",
          "payment_method": "credit_card",
          "amount": ${amount_cents},
          "paid_amount": 0,
          "refunded_amount": 0,
          "last_transaction": {
            "id": "tran_dev0000000099",
            "status": "not_authorized",
            "gateway_response": {
              "code": "1000",
              "errors": [
                { "message": "Insufficient funds" }
              ]
            }
          }
        }
      ],
      "metadata": {
        "platformVersion": "Magento 2 (dev-local)"
      }
    }
  },
  "account": {
    "id": "acc_devtest00000001",
    "name": "Conta Dev Local"
  }
}
JSON
      ;;

    "charge.refunded")
      cat <<JSON
{
  "id": "${hook_id}",
  "type": "charge.refunded",
  "created_at": "${ts}",
  "data": {
    "order": {
      "id": "${pagarme_order_id}",
      "code": "${order_id}",
      "status": "partial_canceled"
    },
    "charge": {
      "id": "${charge_id}",
      "code": "${order_id}",
      "status": "refunded",
      "payment_method": "credit_card",
      "amount": ${amount_cents},
      "paid_amount": ${amount_cents},
      "refunded_amount": ${amount_cents},
      "last_transaction": {
        "id": "tran_dev0000000002",
        "status": "reversed"
      }
    }
  },
  "account": {
    "id": "acc_devtest00000001",
    "name": "Conta Dev Local"
  }
}
JSON
      ;;

    *)
      err "Tipo de evento desconhecido: '${event_type}'"
      err "Use: order.paid | order.payment_failed | charge.refunded"
      exit 1
      ;;
  esac
}

# ─── parse de argumentos ──────────────────────────────────────────────────────
[ $# -lt 1 ] && usage

ORDER_ID=""
EVENT_TYPE="$DEFAULT_TYPE"
AMOUNT="$DEFAULT_AMOUNT"
SKIP_SIG=false
VERBOSE=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    -h|--help)        usage ;;
    -s|--skip-sig)    SKIP_SIG=true;  shift ;;
    -v|--verbose)     VERBOSE=true;   shift ;;
    -t|--type)        EVENT_TYPE="$2"; shift 2 ;;
    -a|--amount)      AMOUNT="$2";    shift 2 ;;
    -u|--url)         BASE_URL="$2"; ENDPOINT="${BASE_URL}/rest/V1/pagarme/webhook"; shift 2 ;;
    -*)
      err "Opção desconhecida: $1"
      usage
      ;;
    *)
      ORDER_ID="$1"
      shift
      ;;
  esac
done

[ -z "$ORDER_ID" ] && { err "ORDER_INCREMENT_ID é obrigatório."; usage; }

# ─── verificar dependências ───────────────────────────────────────────────────
for cmd in curl openssl bc; do
  command -v "$cmd" &>/dev/null || { err "Dependência não encontrada: ${cmd}"; exit 1; }
done

# ─── início ───────────────────────────────────────────────────────────────────
section "Simulador de Webhook Pagar.me"
info "Pedido    : ${ORDER_ID}"
info "Evento    : ${EVENT_TYPE}"
info "Valor     : R\$ ${AMOUNT}"
info "Endpoint  : ${ENDPOINT}"

# ─── montar payload ───────────────────────────────────────────────────────────
BODY=$(build_payload "$ORDER_ID" "$EVENT_TYPE" "$AMOUNT")

section "Payload enviado"
echo "$BODY"

# ─── assinar ou pular ─────────────────────────────────────────────────────────
CURL_ARGS=(-s -k -X POST "$ENDPOINT"
  -H "Content-Type: application/json"
  -H "Accept: application/json"
)

if $SKIP_SIG; then
  warn "--skip-sig ativo: enviando sem assinatura RSA."
  warn "O módulo retornará 401 a menos que você comente o bloco de validação"
  warn "em Model/WebhookManagement.php (linhas 67-91) antes do teste."
  CURL_ARGS+=(-H "X-Webhook-Asymmetric-Signature: skip-for-dev-bypass")
else
  ensure_test_keys
  info "Assinando payload com chave local (${PRIVATE_KEY})..."
  SIG=$(sign_payload "$BODY")
  CURL_ARGS+=(-H "X-Webhook-Asymmetric-Signature: ${SIG}")
  ok "Assinatura gerada (${#SIG} chars)"
fi

CURL_ARGS+=(-d "$BODY")

$VERBOSE && CURL_ARGS=(-v "${CURL_ARGS[@]}")

# ─── enviar ───────────────────────────────────────────────────────────────────
section "Resposta do servidor"

HTTP_RESPONSE=$(curl "${CURL_ARGS[@]}" -w "\n__HTTP_STATUS__%{http_code}" 2>&1)
HTTP_STATUS=$(echo "$HTTP_RESPONSE" | grep '__HTTP_STATUS__' | tail -1 | sed 's/.*__HTTP_STATUS__//')
BODY_RESPONSE=$(echo "$HTTP_RESPONSE" | grep -v '__HTTP_STATUS__')

echo "$BODY_RESPONSE"
echo

case "$HTTP_STATUS" in
  200)
    ok "HTTP ${HTTP_STATUS} — Webhook processado com sucesso!"
    ;;
  401)
    err "HTTP ${HTTP_STATUS} — Assinatura inválida ou ausente."
    echo
    echo -e "${YELLOW}Para testes locais sem assinatura válida, faça UMA das opções:${RESET}"
    echo
    echo "  A) Comente temporariamente o bloco de validação no PHP:"
    echo "     Model/WebhookManagement.php → linhas 67-91"
    echo "     e rode novamente com:  $0 ${ORDER_ID} --skip-sig"
    echo
    echo "  B) Configure a chave pública de teste no módulo:"
    echo "     ${PUBLIC_KEY}"
    echo "     e rode sem --skip-sig (a assinatura RSA local será usada)."
    echo
    echo "  C) Use ngrok para expor localhost e envie webhooks reais:"
    echo "     ngrok http https://localhost:8443"
    ;;
  400)
    err "HTTP ${HTTP_STATUS} — Bad Request. Verifique o formato do payload acima."
    ;;
  404)
    err "HTTP ${HTTP_STATUS} — Endpoint não encontrado. O Magento está rodando em ${BASE_URL}?"
    ;;
  *)
    warn "HTTP ${HTTP_STATUS} — Resposta inesperada. Verifique os logs:"
    warn "  docker exec <container> tail -f /var/www/html/var/log/pagarme.log"
    warn "  docker exec <container> tail -f /var/www/html/var/log/exception.log"
    ;;
esac

echo
