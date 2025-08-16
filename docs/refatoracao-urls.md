# Refatoração de URLs e Limpeza de Rotas

## Resumo das Alterações

Esta refatoração remove URLs hardcoded e rotas não utilizadas do projeto, configurando todas as URLs através de variáveis de ambiente para melhor flexibilidade e manutenibilidade.

## URLs Movidas para Variáveis de Ambiente

### Backend (.env)
- `PRODUCTION_DOMAIN`: Domínio de produção
- `PRODUCTION_API_URL`: URL da API em produção  
- `PRODUCTION_FRONTEND_URL`: URL do frontend em produção
- `GOOGLE_REDIRECT_URI`: Usa variáveis de ambiente dinâmicas
- `GITHUB_REDIRECT_URI`: Usa variáveis de ambiente dinâmicas
- `LINKEDIN_REDIRECT_URI`: Usa variáveis de ambiente dinâmicas

### Frontend (.env)
- `VITE_FRONTEND_URL`: URL do frontend
- `VITE_PRODUCTION_DOMAIN`: Domínio de produção
- `VITE_PRODUCTION_API_URL`: URL da API em produção
- `VITE_PRODUCTION_FRONTEND_URL`: URL do frontend em produção

## Arquivos Removidos

### Rotas de Teste
- `backend/routes/test.php`: Continha rotas `/test-auth-redirect` e `/test-resend` não utilizadas

### Arquivos de Teste HTML
- `frontend/public/teste-token.html`
- `frontend/public/resend-teste.html`
- `frontend/public/newsletter-teste.html`

## Arquivos Atualizados

### Configurações
- `backend/config/cors.php`: URLs dinâmicas baseadas em variáveis de ambiente
- `backend/config/sanctum.php`: Domínios stateful dinâmicos
- `backend/config/app.php`: ✅ Já usava variáveis de ambiente

### Middlewares
- `backend/app/Http/Middleware/Cors.php`: Lógica robusta para origins permitidas
- `backend/app/Http/Middleware/ApiAuthenticate.php`: URLs dinâmicas
- `backend/app/Http/Middleware/HandleOptions.php`: URLs dinâmicas

### Controllers
- `backend/app/Http/Controllers/BaseDocumentationController.php`: URLs dinâmicas no Swagger
- `backend/app/Http/Controllers/Auth/SocialAuthController.php`: URLs dinâmicas

### Frontend
- `frontend/index.html`: Variáveis de ambiente para meta tags e reCAPTCHA
- `frontend/vite.config.ts`: Configuração para substituir variáveis no HTML
- `frontend/src/utils/urlBuilder.ts`: Suporte para URLs de produção

### Outros
- `backend/public/.htaccess`: CORS comentado (gerido pelo middleware)

## Benefícios das Alterações

1. **Flexibilidade**: URLs podem ser alteradas via variáveis de ambiente
2. **Segurança**: Sem URLs hardcoded no código
3. **Manutenibilidade**: Configuração centralizada
4. **Deploy**: Fácil configuração para diferentes ambientes
5. **Limpeza**: Código mais limpo sem rotas não utilizadas

## Configuração de Ambiente

### Desenvolvimento
```env
# Backend
APP_URL=http://localhost:8001
APP_FRONTEND_URL=http://localhost:5174

# Frontend  
VITE_API_URL=http://localhost:8001
VITE_FRONTEND_URL=http://localhost:5174
```

### Produção
```env
# Backend
APP_URL=https://lucas-cv.iwork.pt
APP_FRONTEND_URL=https://lucas-cv.iwork.pt
PRODUCTION_DOMAIN=lucas-cv.iwork.pt
PRODUCTION_API_URL=https://lucas-cv.iwork.pt
PRODUCTION_FRONTEND_URL=https://lucas-cv.iwork.pt

# Frontend
VITE_API_URL=https://lucas-cv.iwork.pt
VITE_FRONTEND_URL=https://lucas-cv.iwork.pt
```

## Próximos Passos

1. Testar todas as funcionalidades em desenvolvimento
2. Verificar se os redirects de autenticação social funcionam
3. Confirmar que o CORS está funcionando corretamente
4. Validar a documentação Swagger com URLs dinâmicas
5. Testar em produção com as novas configurações

## Compatibilidade

As alterações mantêm compatibilidade com as configurações existentes através de fallbacks. Se as novas variáveis não estiverem definidas, o sistema usa as URLs padrão.
