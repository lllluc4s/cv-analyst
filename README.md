# CV Analyst - Estrutura Unificada

Este projeto foi reestruturado para uma arquitetura unificada Laravel + Vue.js, onde todo o código (backend e frontend) está em um único diretório.

## 📁 Estrutura do Projeto

```
cv-analyst/
├── app/                    # Classes PHP do Laravel
├── resources/
│   ├── views/             # Templates Blade
│   ├── css/               # CSS do Laravel
│   ├── js/                # JavaScript do Laravel  
│   └── frontend/          # Aplicação Vue.js
│       ├── src/
│       │   ├── components/
│       │   ├── views/
│       │   ├── router/
│       │   └── services/
│       ├── package.json
│       └── vite.config.ts
├── public/                # Arquivos públicos
│   ├── index.php         # Entry point principal
│   └── build/            # Assets compilados
├── routes/                # Rotas Laravel
├── database/              # Migrações e seeders
├── config/                # Configurações
├── storage/               # Armazenamento
└── vendor/                # Dependencies PHP
```

## 🚀 Como Funciona

### Entry Point
- O `public/index.php` do Laravel é o único ponto de entrada
- Todas as rotas passam pelo Laravel primeiro
- As rotas da SPA são tratadas por um catch-all que serve o template `app.blade.php`

### Frontend Integration
- O Vue.js está integrado via Laravel Vite
- Os assets são servidos através do sistema de build do Laravel
- A aplicação Vue funciona como SPA dentro do Laravel

## 🛠️ Comandos de Desenvolvimento

### Instalação
```bash
# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install
```

### Desenvolvimento
```bash
# Iniciar servidor Laravel
php artisan serve

# Em outro terminal, iniciar Vite para hot reload
npm run dev
```

### Produção
```bash
# Build dos assets
npm run build

# Otimizar Laravel
php artisan optimize
```

## 🔧 Configuração

### Variáveis de Ambiente
No arquivo `.env`, configure:

```env
APP_URL=http://localhost:8000
VITE_API_URL=http://localhost:8000
```

### CORS
O arquivo `config/cors.php` já está configurado para aceitar requisições do frontend.

## 📝 Mudanças da Reestruturação

1. **Frontend movido**: De `frontend/` para `resources/frontend/`
2. **Build integrado**: Assets compilados vão para `public/build/`
3. **Rotas unificadas**: Todas passam pelo Laravel
4. **CSRF configurado**: Token automático para requisições AJAX
5. **URLs relativas**: Frontend usa URLs baseadas no domínio atual

## 🌐 Produção

Em produção, apenas a pasta `public/` precisa ser exposta pelo servidor web. O Laravel cuidará de:
- Servir a aplicação Vue.js
- Processar APIs
- Gerenciar autenticação
- Servir assets estáticos

## 🔒 Segurança

- CSRF protection habilitado
- Assets com hash para cache busting
- Autenticação via Laravel Sanctum
- Configuração de CORS apropriada

---

A estrutura agora é mais simples, mais segura e mais fácil de fazer deploy!
