#!/bin/bash

# ==============================================================================
# Configuração
# ==============================================================================
MODULE_NAME="pagarme/pagarme-magento2-module"
MODULE_COMPOSER_PATH="vendor/pagarme/pagarme-magento2-module/composer.json"
PATCH_FILE="pagarme_temp.patch"
FIXED_PATCH_URL="https://gist.github.com/fabiano-mallmann/c9fe6e1607dbf69574b9fe9d0c5d1eb1/raw"
PATCH_LEVEL="-p1"

# URL do Patch Condicional (Será definida com base na versão)
CONDITIONAL_PATCH_URL=""

# ==============================================================================
# Funções de Auxílio
# ==============================================================================

# Função para comparar versões (semver) de forma compatível
# Retorna 0 (true) se a versão estiver na faixa
function version_in_range() {
    local version="$1"
    local range_start="$2"
    local range_end="$3"

    # Se range_start for igual a range_end, verifica se é a versão exata
    if [ "$range_start" = "$range_end" ]; then
        [ "$version" = "$range_start" ] && return 0
        return 1
    fi

    # Compara se version >= range_start E version <= range_end
    # Usa 'sort -V' para comparação de versão semântica
    # Verifica se $version >= $range_start
    if [ "$(printf '%s\n' "$range_start" "$version" | sort -V | head -n1)" = "$range_start" ] && \
       [ "$(printf '%s\n' "$version" "$range_end" | sort -V | head -n1)" = "$version" ]; then
        return 0
    fi
    return 1
}

# Função para baixar e aplicar o patch
function download_and_apply() {
    local patch_url="$1"
    local patch_name="$2"
    local patch_file="${PATCH_FILE}_${patch_name}.patch"

    echo "--------------------------------------------------------"
    echo "  >> Processando Patch: $patch_name"
    echo "--------------------------------------------------------"
    echo "URL: $patch_url"

    # Usar curl ou wget
    if command -v curl >/dev/null 2>&1; then
        curl -sSL "$patch_url" -o "$patch_file"
    elif command -v wget >/dev/null 2>&1; then
        wget -q -O "$patch_file" "$patch_url"
    else
        echo "ERRO: Nem 'curl' nem 'wget' foram encontrados. Instale um deles."
        exit 1
    fi

    if [ $? -ne 0 ] || [ ! -s "$patch_file" ]; then
        echo "ERRO: Falha ao baixar o patch $patch_name. Verifique a URL."
        return 1
    fi
    
    # Executa o patch com dry-run primeiro para verificar
    echo "Verificando aplicação (dry-run)..."
    patch $PATCH_LEVEL --dry-run < "$patch_file"
    if [ $? -ne 0 ]; then
        echo "ERRO: O patch $patch_name não pode ser aplicado (dry-run falhou)."
        echo "O arquivo de patch ($patch_file) foi mantido para inspeção."
        return 1
    fi

    # Aplica o patch
    echo "Aplicando o patch ($patch_name) com nível $PATCH_LEVEL..."
    patch $PATCH_LEVEL --verbose < "$patch_file"
    
    if [ $? -eq 0 ]; then
        echo "SUCESSO: Patch $patch_name aplicado."
        rm "$patch_file"
    else
        echo "ERRO: Falha ao aplicar o patch $patch_name."
        echo "O arquivo de patch ($patch_file) foi mantido para inspeção."
        return 1
    fi

    return 0
}

# ==============================================================================
# Lógica Principal
# ==============================================================================

# 1. Obter a versão do módulo
echo "Buscando versão do módulo $MODULE_NAME no arquivo $MODULE_COMPOSER_PATH..."
if [ ! -f "$MODULE_COMPOSER_PATH" ]; then
    echo "ERRO: O arquivo $MODULE_COMPOSER_PATH não foi encontrado."
    echo "Certifique-se de que está na raiz do projeto Magento e que o módulo está instalado."
    exit 1
fi

# Usa AWK para extrair o valor da chave "version" do JSON, de forma compatível com Bash/Shell.
MODULE_VERSION=$(awk -F'"' '/"version":/ {print $4; exit}' "$MODULE_COMPOSER_PATH" || echo "")

if [ -z "$MODULE_VERSION" ]; then
    echo "ERRO: Não foi possível extrair a versão de $MODULE_COMPOSER_PATH."
    exit 1
fi

echo "Versão do módulo encontrada: $MODULE_VERSION"

# 2. Mapeamento da URL do Patch Condicional
# Usando if/elif/else e a função version_in_range
# A função sort -V garante que a ordem de comparação seja semântica (2.2.5 é maior que 2.2.4)

# Da versão 1.1.0 até a 2.2.4
if version_in_range "$MODULE_VERSION" "1.1.0" "2.2.4"; then
    CONDITIONAL_PATCH_URL="https://gist.github.com/fabiano-mallmann/a7eef791b1640dbcc54617a16f9b6faf/raw"
    echo "Faixa de versão correspondente: 1.1.0 - 2.2.4"
# Na versão 2.2.5
elif version_in_range "$MODULE_VERSION" "2.2.5" "2.2.5"; then
    CONDITIONAL_PATCH_URL="https://gist.github.com/fabiano-mallmann/fb27fa2cf43d6214f2930ef6fa301596/raw"
    echo "Versão correspondente: 2.2.5"
# Da versão 2.3.0 até a 2.4.2 
elif version_in_range "$MODULE_VERSION" "2.3.0" "2.4.2"; then
    CONDITIONAL_PATCH_URL="https://gist.github.com/fabiano-mallmann/f6697544aa407646275f84bc4eb9de31/raw"
    echo "Faixa de versão correspondente: 2.3.0 - 2.4.2"
# Da versão 2.5.0 até 2.7.2
elif version_in_range "$MODULE_VERSION" "2.5.0" "2.7.2"; then
    CONDITIONAL_PATCH_URL="https://gist.github.com/fabiano-mallmann/76674cfbcf3ac111cb4634497aa1fb1e/raw"
    echo "Faixa de versão correspondente: 2.5.0 - 2.7.2"
else
    echo "ERRO: Nenhuma faixa de patch definida corresponde à versão $MODULE_VERSION. O script não pode prosseguir."
    exit 1
fi

# 3. Aplicar o patch condicional
download_and_apply "$CONDITIONAL_PATCH_URL" "conditional"
if [ $? -ne 0 ]; then
    echo "Processo de aplicação de patch abortado."
    exit 1
fi

# 4. Aplicar o patch fixo
download_and_apply "$FIXED_PATCH_URL" "fixed"
if [ $? -ne 0 ]; then
    echo "Processo de aplicação de patch abortado."
    exit 1
fi

echo "--------------------------------------------------------"
echo "  ✅ TODOS OS PATCHES FORAM APLICADOS COM SUCESSO!     "
echo "--------------------------------------------------------"
echo "Próximo passo: Limpar o cache do Magento."
echo "Comando sugerido: php bin/magento cache:clean"

exit 0