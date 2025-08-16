# Correções de URLs - Relatório

## 🐛 Problema Encontrado
URLs com `/api` duplicado resultando em erro 404:
- ❌ `http://localhost:8001/api/api/public/skills`
- ✅ `http://localhost:8001/api/public/skills`

## 🔧 Arquivos Corrigidos

### Views (.vue)
1. `OportunidadesPublic.vue`
   - `/api/public/companies` → `/public/companies`
   - `/api/public/skills` → `/public/skills`
   - `/api/public/oportunidades` → `/public/oportunidades`

2. `OportunidadesPublicSimple.vue`
   - `/api/public/oportunidades` → `/public/oportunidades`

3. `CompaniesPublic.vue`
   - `/api/public/empresas` → `/public/empresas`

4. `CompanyPublic.vue`
   - `/api/public/empresa/` → `/public/empresa/`

5. `CvAnalyzer.vue`
   - `/api/analyze-cvs` → `/analyze-cvs`

### Services (.ts)
6. `AuthService.ts`
   - `/api/auth/status` → `/auth/status`
   - `/api/user/profile` → `/user/profile`
   - `/api/candidaturas` → `/candidaturas`

## ✅ Explicação
O `ApiService` já configura o `axios.defaults.baseURL` com `UrlBuilder.api()` que inclui `/api`.
Portanto, as requisições devem usar URLs relativas sem `/api`.

## 🧪 Teste
```bash
# Testar APIs funcionando
curl http://localhost:8001/api/public/skills
curl http://localhost:8001/api/public/companies
curl http://localhost:8001/api/public/oportunidades
```

Agora todas as requisições devem funcionar sem erro 404! 🎉
