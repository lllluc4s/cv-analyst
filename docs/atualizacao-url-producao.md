# Atualização de URL de Produção

## Resumo da Alteração

URL de produção atualizada de `https://cv-lucas.iwork.pt/` para `https://lucas-cv.iwork.pt/`

## Arquivos Atualizados

### Backend
1. **`.env`**
   - `PRODUCTION_DOMAIN`: cv-lucas.iwork.pt → lucas-cv.iwork.pt
   - `PRODUCTION_API_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt
   - `PRODUCTION_FRONTEND_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt
   - `SANCTUM_STATEFUL_DOMAINS`: Atualizado com novo domínio

2. **`config/cors.php`**
   - Fallback domain atualizado para lucas-cv.iwork.pt

3. **`app/Http/Controllers/BaseDocumentationController.php`**
   - URLs do Swagger atualizadas para nova URL
   - Fallbacks atualizados nos métodos dinâmicos

### Frontend
1. **`.env`**
   - `VITE_PRODUCTION_DOMAIN`: cv-lucas.iwork.pt → lucas-cv.iwork.pt
   - `VITE_PRODUCTION_API_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt
   - `VITE_PRODUCTION_FRONTEND_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt

2. **`.env.production`**
   - `VITE_API_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt
   - `VITE_FRONTEND_URL`: https://cv-lucas.iwork.pt → https://lucas-cv.iwork.pt
   - Todas as variáveis de produção atualizadas

### Documentação
1. **`docs/refatoracao-urls.md`**
   - Exemplos de configuração atualizados

2. **`storage/api-docs/api-docs.json`**
   - Documentação Swagger regenerada com nova URL

### Arquivos Removidos
1. **`app/Http/Controllers/SwaggerController.php`**
   - Removido por duplicar configuração do BaseDocumentationController

## Configuração Necessária para Deploy

### Variáveis de Ambiente para Produção
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

### Configurações do Servidor/DNS
- Certificado SSL para `lucas-cv.iwork.pt`
- Redirecionamento de `cv-lucas.iwork.pt` (se necessário)
- Atualização das configurações OAuth (Google, GitHub, LinkedIn)

## Próximos Passos

1. ✅ Atualizar configurações OAuth nos provedores:
   - Google Console: Alterar redirect URI
   - GitHub: Alterar redirect URI  
   - LinkedIn: Alterar redirect URI

2. ✅ Configurar DNS para `lucas-cv.iwork.pt`

3. ✅ Configurar certificado SSL

4. ✅ Testar todas as funcionalidades após deploy

## Compatibilidade

- ✅ Mantém compatibilidade com desenvolvimento (localhost)
- ✅ URLs antigas removidas gradualmente
- ✅ Variáveis de ambiente bem definidas
- ✅ Documentação atualizada
