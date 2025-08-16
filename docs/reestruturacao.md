# Documentação da Reestruturação

## ✅ O que foi feito

### 1. Arquitetura Separada mas Unificada
- ✅ Frontend Vue.js mantido em `resources/frontend/` (SPA independente)
- ✅ Backend Laravel na raiz servindo apenas API
- ✅ Estrutura unificada em um só repositório
- ✅ Desenvolvimento com dois servidores (Laravel + Vite)

### 2. Configuração do Desenvolvimento
- ✅ Laravel roda na porta 8001 (API)
- ✅ Vue.js roda na porta 5174/5175 (Frontend)
- ✅ Proxy configurado para `/api/*` apontar para Laravel
- ✅ Hot reload funcionando no frontend

### 3. Roteamento
- ✅ Laravel serve apenas APIs em `/api/*`
- ✅ Vue.js Router gerencia todas as rotas do frontend
- ✅ Autenticação via Laravel Sanctum
- ✅ CORS configurado para comunicação entre servidores

### 4. Build de Produção
- ✅ Frontend compila para `public/frontend/`
- ✅ Laravel serve tanto API quanto frontend compilado
- ✅ Assets otimizados com Vite

## 🎯 Arquitetura

```
Desenvolvimento:
Frontend (Vue.js) :5175 ←→ API (Laravel) :8001

Produção:
Laravel :80 serve ambos:
├── API em /api/*
└── Frontend compilado em /*
```

## 🚀 Como usar

### Desenvolvimento Diário
```bash
# Opção 1: Script automático
./start.sh

# Opção 2: Manual
php artisan serve --port=8001     # Terminal 1
cd resources/frontend && npm run dev    # Terminal 2
```

### Primeira vez
```bash
./dev-setup.sh    # Apenas uma vez
```

### Produção
```bash
./deploy.sh       # Deploy completo
```

## 🌐 URLs de Desenvolvimento

- **Frontend**: http://localhost:5175
- **API**: http://localhost:8001/api
- **Documentação API**: http://localhost:8001/docs

## ✨ Benefícios da Arquitetura

1. **Frontend independente**: Vue.js funciona como SPA completo
2. **Hot reload**: Desenvolvimento rápido com Vite
3. **API clara**: Laravel só gerencia dados e autenticação
4. **Deploy simples**: Tudo em um lugar na produção
5. **Escalabilidade**: Fácil de separar no futuro se necessário

## � Configuração

### Frontend (.env)
```env
VITE_API_URL=http://localhost:8001
```

### Backend (.env)
```env
APP_URL=http://localhost:8001
FRONTEND_URL=http://localhost:5175
```

A aplicação Vue.js agora funciona completamente independente com todos os estilos! 🎉
