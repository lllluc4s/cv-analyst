#!/bin/bash

# Script de Deploy - CV Analyst
# Execute este script em produção para preparar a aplicação

echo "🚀 Iniciando deploy do CV Analyst..."

# 1. Instalar dependências PHP
echo "📦 Instalando dependências PHP..."
composer install --no-dev --optimize-autoloader

# 2. Instalar dependências Node.js
echo "📦 Instalando dependências Node.js..."
npm ci

# 3. Build do frontend
echo "🏗️ Fazendo build do frontend..."
npm run build

# 4. Configurar ambiente Laravel
echo "⚙️ Configurando Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Executar migrações (se necessário)
echo "🗄️ Executando migrações..."
php artisan migrate --force

# 6. Otimizar aplicação
echo "⚡ Otimizando aplicação..."
php artisan optimize

# 7. Configurar permissões
echo "🔐 Configurando permissões..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deploy concluído!"
echo "📝 Lembre-se de:"
echo "   - Configurar o servidor web para apontar para a pasta 'public/'"
echo "   - Configurar as variáveis de ambiente no arquivo .env"
echo "   - Verificar se o SSL está configurado para HTTPS"
