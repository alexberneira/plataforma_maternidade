#!/bin/bash

# CONFIGURAÇÕES - EDITE CONFORME SUA VPS
REPO_URL="https://github.com/alexberneira/plataforma_maternidade.git"
PASTA_SITE="$HOME/public_html" # ou /home/SEU_USUARIO/seusite.com.br/public_html
DB_NAME="plataforma_maternidade"
DB_USER="usuario_do_banco"
DB_PASS="senha_do_banco"
DUMP_LOCAL="dump.sql" # Caminho do dump do banco, se for importar
ENV_EXEMPLO=".env.example" # Se quiser copiar um modelo
ENV_REAL=".env" # Nome do arquivo real de env

echo "==== DEPLOY PLATAFORMA MATERNIDADE ===="

# 1. Instalar dependências básicas
echo ">> Instalando dependências básicas (git, unzip, curl)..."
sudo apt-get update
sudo apt-get install -y git unzip curl

# 2. Instalar Composer (se não existir)
if ! command -v composer &> /dev/null; then
    echo ">> Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# 3. Clonar ou atualizar o repositório
if [ ! -d "$PASTA_SITE/.git" ]; then
    echo ">> Clonando repositório..."
    git clone "$REPO_URL" "$PASTA_SITE"
else
    echo ">> Atualizando repositório..."
    cd "$PASTA_SITE"
    git pull
fi

cd "$PASTA_SITE"

# 4. Instalar dependências PHP
echo ">> Instalando dependências do Composer..."
composer install --no-interaction --prefer-dist

# 5. Configurar variáveis de ambiente
if [ ! -f "$ENV_REAL" ] && [ -f "$ENV_EXEMPLO" ]; then
    echo ">> Copiando arquivo de exemplo de env..."
    cp "$ENV_EXEMPLO" "$ENV_REAL"
    echo ">> Edite o arquivo $ENV_REAL com suas chaves e configs!"
fi

# 6. Importar banco de dados (opcional)
if [ -f "$DUMP_LOCAL" ]; then
    echo ">> Importando banco de dados..."
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$DUMP_LOCAL"
fi

# 7. Ajustar permissões
echo ">> Ajustando permissões da pasta uploads..."
mkdir -p uploads
chmod -R 755 uploads

echo ">> DEPLOY FINALIZADO! Acesse seu domínio para testar." 