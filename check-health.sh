#!/bin/bash

echo "🔍 Verificando se tudo está funcionando..."

# Testar Laravel
echo "📡 Testando Laravel API..."
if curl -s "http://localhost:8001/api/public/oportunidades" > /dev/null; then
    echo "✅ Laravel API: OK"
else
    echo "❌ Laravel API: FALHOU"
    echo "💡 Execute: php artisan serve --port=8001"
fi

# Testar Frontend
echo "🖥️  Testando Frontend..."
if curl -s "http://localhost:5174" > /dev/null; then
    echo "✅ Frontend Vue.js: OK"
else
    echo "❌ Frontend Vue.js: FALHOU"
    echo "💡 Execute: cd resources/frontend && npm run dev"
fi

# Testar CORS
echo "🌐 Testando CORS..."
CORS_RESPONSE=$(curl -s -H "Origin: http://localhost:5174" -H "Access-Control-Request-Method: GET" -X OPTIONS "http://localhost:8001/api/public/oportunidades" -i | grep "Access-Control-Allow-Origin")
if [[ $CORS_RESPONSE == *"http://localhost:5174"* ]]; then
    echo "✅ CORS: OK"
else
    echo "❌ CORS: FALHOU"
fi

echo ""
echo "📝 URLs importantes:"
echo "   🖥️  Frontend: http://localhost:5174"
echo "   📡 API: http://localhost:8001/api"
echo "   📚 Docs: http://localhost:8001/docs"
