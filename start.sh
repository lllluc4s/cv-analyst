#!/bin/bash

# Script de Desenvolvimento Diário - CV Analyst
# Execute este script para iniciar rapidamente o desenvolvimento

echo "🚀 Iniciando CV Analyst..."

# Verificar se está tudo instalado
if [ ! -d "vendor" ] || [ ! -d "node_modules" ]; then
    echo "⚠️  Dependências não encontradas. Execute primeiro: ./dev-setup.sh"
    exit 1
fi

# Verificar se o .env existe
if [ ! -f ".env" ]; then
    echo "⚠️  Arquivo .env não encontrado. Execute primeiro: ./dev-setup.sh"
    exit 1
fi

echo "🌐 Laravel API: http://localhost:8001"
echo "🖥️  Vue.js Frontend: http://localhost:5174"
echo "⚡ Ambos os servidores iniciando..."
echo ""
echo "💡 Para parar, pressione Ctrl+C"
echo ""

# Iniciar Laravel em background
php artisan serve --host=0.0.0.0 --port=8001 &
LARAVEL_PID=$!

# Mudar para o diretório do frontend e iniciar Vite
cd resources/frontend
npm run dev &
NPM_PID=$!

# Voltar ao diretório raiz
cd ../..

# Função para limpar processos ao sair
cleanup() {
    echo ""
    echo "🛑 Parando serviços..."
    kill $LARAVEL_PID 2>/dev/null
    kill $NPM_PID 2>/dev/null
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT

# Aguardar até o usuário pressionar Ctrl+C
wait
