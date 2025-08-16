#!/bin/bash

# Script de Desenvolvimento - CV Analyst
# Execute este script para iniciar o ambiente de desenvolvimento

echo "🛠️ Iniciando ambiente de desenvolvimento..."

# Verificar se as dependências estão instaladas
if [ ! -d "vendor" ]; then
    echo "📦 Instalando dependências PHP..."
    sudo composer install
fi

if [ ! -d "node_modules" ]; then
    echo "📦 Instalando dependências Node.js..."
    sudo npm install
fi

# Verificar se o .env existe
if [ ! -f ".env" ]; then
    echo "⚙️ Criando arquivo .env..."
    cp .env.example .env
    php artisan key:generate
fi

# Executar migrações se necessário
echo "🗄️ Verificando banco de dados..."
php artisan migrate

echo "✅ Ambiente pronto!"
echo ""
echo "📝 Para iniciar o desenvolvimento:"
echo "   1. Execute: php artisan serve"
echo "   2. Em outro terminal: npm run dev"
echo ""
echo "🌐 A aplicação estará disponível em: http://localhost:8001"
